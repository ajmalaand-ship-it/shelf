<?php

namespace App\Filament\Resources\Collections\RelationManagers;

use App\Support\WordImport\DocxReader;
use App\Support\WordImport\ImportWordDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

trait HasWordImport
{
    #[Locked]
    public ?array $wordPreview = null;

    protected function wordImportAction(): Action
    {
        return Action::make('importWord')->label('Import from Word')
            ->authorize(fn (): bool => $this->canCreate() && ! $this->getOwnerRecord()->trashed())
            ->disabled(fn (): bool => ! Schema::hasTable('word_imports'))
            ->modalHeading('Import from Word')->modalSubmitActionLabel('Preview')
            ->schema([
                FileUpload::make('document')->label('Word document (.docx)')->required()
                    ->disk('temporary')->storeFiles(false)->maxSize(20480)
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'])
                    ->rules(['extensions:docx'])
                    ->helperText('One .docx file, up to 20 MB. Heading 1 or a line containing only *** starts a new item. Nothing is imported until you review the preview and click Import.'),
            ])
            ->action(function (array $data): void {
                $file = $data['document'];
                if (! $file instanceof TemporaryUploadedFile || strtolower($file->getClientOriginalExtension()) !== 'docx') {
                    throw ValidationException::withMessages(['document' => 'Choose one .docx file.']);
                }
                try {
                    $result = app(DocxReader::class)->read($file->getRealPath());
                } catch (ValidationException $error) {
                    throw ValidationException::withMessages([
                        $this->getMountedActionSchema()->getStatePath().'.document' => $error->getMessage(),
                    ]);
                }
                $this->wordPreview = [
                    'id' => (string) Str::uuid(), 'path' => $file->getRealPath(),
                    'upload' => $file->getFilename(), 'filename' => $file->getClientOriginalName(), 'sha256' => $result['sha256'],
                    'warnings' => $result['warnings'],
                    'items' => array_map(fn (array $item): array => array_diff_key($item, ['body' => true]), $result['items']),
                ];
                $this->replaceMountedAction('confirmWordImport');
            });
    }

    public function confirmWordImportAction(): Action
    {
        return Action::make('confirmWordImport')
            ->authorize(fn (): bool => $this->wordPreview !== null && $this->canCreate() && ! $this->getOwnerRecord()->trashed())
            ->modalHeading('Preview Word import')->modalSubmitActionLabel('Import')
            ->modalWidth('5xl')
            ->modalContent(fn () => view('filament.imports.word-preview', [
                'preview' => $this->wordPreview,
                'label' => $this->getOwnerRecord()->book_type === 'prose' ? 'Chapter' : 'Poem',
            ]))
            ->modalSubmitAction(fn (Action $action) => $action->disabled(empty($this->wordPreview['items'])))
            ->action(function (Action $action): void {
                try {
                    $import = app(ImportWordDocument::class)->import($this->getOwnerRecord()->getKey(), auth()->user(), $this->wordPreview);
                } catch (ValidationException $error) {
                    Notification::make()->danger()->title('Nothing imported')->body($error->getMessage())->send();
                    $action->halt();
                } catch (Throwable $error) {
                    report($error);
                    Notification::make()->danger()->title('Import failed')
                        ->body('No items were added. An archived original, if created, has been retained. You can retry this preview.')->send();
                    $action->halt();
                }
                $this->resetTable();
                Notification::make()->success()->title($import->item_count.' items imported as drafts')->send();
            });
    }

    public function unmountAction(bool|string|null $cancelParentActions = null): void
    {
        parent::unmountAction($cancelParentActions);
        if ($this->mountedActions === [] && $this->wordPreview !== null) {
            Storage::disk(FileUploadConfiguration::disk())->delete(
                FileUploadConfiguration::path($this->wordPreview['upload']),
            );
            $this->wordPreview = null;
        }
    }
}
