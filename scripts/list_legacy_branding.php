<?php

// Owner-run, read-only inventory. Reports locations/counts, never stored values.
// Run from any directory: php /home/shelf/apps/shelf/scripts/list_legacy_branding.php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $connection = DB::connection();
    if ($connection->getDriverName() !== 'mysql' || $connection->getDatabaseName() !== 'shelf_app') {
        throw new RuntimeException('Unexpected database target.');
    }

    $columns = $connection->select(
        'SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND DATA_TYPE IN
             (\'char\', \'varchar\', \'tinytext\', \'text\', \'mediumtext\', \'longtext\', \'json\')
         ORDER BY TABLE_NAME, ORDINAL_POSITION',
        ['shelf_app'],
    );
    $matches = 0;
    foreach ($columns as $column) {
        $query = $connection->table($column->table_name)->where(function ($query) use ($column) {
            $name = $query->getGrammar()->wrap($column->column_name);
            $query->whereRaw("LOWER($name) LIKE ?", ['%pitswal%'])
                ->orWhereRaw("LOWER($name) LIKE ?", ['%poetry.ajmalaand.com%']);
            // The old app also used this Pashto brand in app_settings.
            if ($column->table_name === 'app_settings' && $column->column_name === 'value') {
                $query->orWhere($column->column_name, 'like', '%پېڅوَل%');
            }
        });
        $count = (clone $query)->count();
        if ($count === 0) {
            continue;
        }
        $matches += $count;
        echo json_encode([
            'table' => $column->table_name,
            'column' => $column->column_name,
            'matching_rows' => $count,
        ], JSON_THROW_ON_ERROR).PHP_EOL;
        if ($column->table_name === 'app_settings') {
            foreach ((clone $query)->pluck('key') as $key) {
                echo json_encode(['app_settings_key' => $key], JSON_THROW_ON_ERROR).PHP_EOL;
            }
        }
    }
    echo 'Matching cells: '.$matches.'. No data changed; stored values withheld.'.PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Inventory failed; connection and exception details withheld.'.PHP_EOL);
    exit(1);
}
