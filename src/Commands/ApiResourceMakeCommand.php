<?php
namespace Smiarowski\Generators\Commands;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use ICanBoogie\Inflector;
use Smiarowski\Generators\Migrations\SchemaParser;
use Smiarowski\Generators\Migrations\SyntaxBuilder;

class ApiResourceMakeCommand extends Command
{
    use AppNamespaceDetectorTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:api-resource {name} {--schema=} {--softdeletes}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new REST API resource: controller, model, request, migration and route';
    
    protected $tableName;
    protected $varModelName;
    protected $varModelNamePlural;
    protected $modelName;
    protected $fileTypes = ['migration', 'controller', 'model', 'request'];
    protected $createdFiles = [];
    protected $validationRules = [];
    
    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Foundation\Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer) {
        parent::__construct();
        
        $this->files = $files;
        $this->composer = $composer;
        $this->inflector = Inflector::get('en');
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        $this->setAttribs();
        foreach ($this->fileTypes as $type) {
            $this->createFile($type);
        }
        $this->addRoute();
        $this->composer->dumpAutoloads();
    }
    
    protected function setAttribs() {
        $name = strtolower($this->argument('name'));
        
        $this->tableName = $this->inflector->pluralize($this->inflector->underscore($name));
        $this->varModelName = $this->inflector->singularize($this->inflector->camelize($name, Inflector::DOWNCASE_FIRST_LETTER));
        $this->varModelNamePlural = $this->inflector->pluralize($this->varModelName);
        $this->modelName = ucfirst($this->varModelName);
        $this->meta = ['action' => 'create', 'table' => $this->tableName];
    }
    
    protected function createFile($type = 'migration') {
        if ($this->files->exists($path = $this->getPath($type))) {
            foreach ($this->createdFiles as $filePath) {
                $this->files->delete($filePath);
            }
            
            $this->error($path . ' already exists!');
            die();
        }
        
        $this->makeDirectory($path);
        $this->files->put($path, $this->compileStub($type));
        $this->createdFiles[] = $path;
        $this->info(ucfirst($type) . ' created successfully.');
    }
    
    protected function addRoute() {
        $line = sprintf("%sRoute::resource('%s', '%sController');%s", PHP_EOL, $this->varModelName, $this->modelName, PHP_EOL);
        $line.= sprintf("Route::put('%s/{%s}', '%sController@store');%s", $this->varModelName, $this->varModelName, $this->modelName, PHP_EOL);
        $this->files->append($this->getPath('route'), $line);
    }
    
    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path) {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }
    }
    
    /**
     * Get the path to files being created.
     *
     * @param  string $type migration|controller|model|request|route
     * @return string
     */
    protected function getPath($type = 'migration') {
        switch ($type) {
            case 'migration':
                return sprintf('%s/database/migrations/%s_create_%s_table.php', base_path(), date('Y_m_d_His'), $this->tableName);
            case 'controller':
                return sprintf('%s/Http/Controllers/%sController.php', app_path(), $this->modelName);
            case 'model':
                return sprintf('%s/%s.php', app_path(), $this->modelName);
            case 'request':
                return sprintf('%s/Http/Requests/%sRequest.php', app_path(), $this->modelName);
            case 'route':
                return sprintf('%s/Http/routes.php', app_path());
        }
    }
    
    /**
     * Compile the migration stub.
     * @param  string $stubName Filename of the stub: migration|controller|model|request
     * @return string
     */
    protected function compileStub($stubName = 'migration') {
        $stub = $this->files->get(sprintf('%s/../stubs/%s.stub', __DIR__, $stubName));
        $this->replaceInStub($stub);
        if ($stubName === 'migration') {
            $this->replaceSchema($stub);
        }
        if ($stubName === 'request') {
            $this->replaceValidationStub($stub);
        }
        
        return $stub;
    }
    
    /**
     * Replace placeholders with proper values
     * @param  string $stub
     * @return void
     */
    protected function replaceInStub(&$stub) {
        $stub = str_replace('{{app_name}}', str_replace('\\', '', $this->getAppNamespace()), $stub);
        $stub = str_replace('{{model_name}}', $this->modelName, $stub);
        $stub = str_replace('{{var_model_name}}', $this->varModelName, $stub);
        $stub = str_replace('{{var_model_name_plural}}', $this->varModelNamePlural, $stub);
        $stub = str_replace('{{table}}', $this->tableName, $stub);
        $stub = str_replace('{{migration_class}}', sprintf('Create%sTable', $this->inflector->camelize($this->tableName)), $stub);
        $stub = str_replace('{{soft_deletes}}', $this->option('softdeletes') ? '$table->softDeletes();' : '', $stub);
        $stub = str_replace('{{guarded_soft_deletes}}', $this->option('softdeletes') ? ', \'deleted_at\'' : '', $stub);
    }

    /**
     * Replace the schema for the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceSchema(&$stub) {
        if ($schema = $this->option('schema')) {
            $schema = (new SchemaParser)->parse($schema);
        }
        $this->buildValidation($schema);
        $stub = (new SyntaxBuilder)->create($schema, $stub);
        
        return $this;
    }

    protected function replaceValidationStub(&$stub)
    {
        $rules = '';
        foreach ($this->validationRules as $column => $rule) {
            $rules .= sprintf('%s\'%s\' => \'%s\',%s', str_repeat(' ', 12), $column, implode('|', $rule), PHP_EOL);
        }
        $stub = str_replace('{{validation_rules}}', $rules, $stub);
    }
    
    protected function buildValidation(array $schema) {
        foreach ($schema as $s) {
            $this->validationRules[$s['name']] = ['required'];
            $this->typeToValidator($s['name'], $s['type']);
            $this->optionsToValidator($s['name'], $s['options']);
        }
    }
    
    /**
     * Finds validation rule for field type specified in schema building
     * @param string $type
     * @return void
     */
    protected function typeToValidator($name, $type) {
        switch ($type) {
            case 'string':
            case 'integer':
            case 'email':
            case 'boolean':
            case 'date':
            case 'json':
                $this->validationRules[$name][] = $type;
                break;
            case 'dateTime':
                $this->validationRules[$name][] = 'date';
                break;
            case 'url':
                $this->validationRules[$name][] = 'text';
                break;
            case 'float':
            case 'double':
            case 'decimal':
                $this->validationRules[$name][] = 'numeric';
                break;
            case 'tinyInteger':
            case 'smallIngeger':
            case 'mediumInteger':
            case 'bigInteger':
                $this->validationRules[$name][] = 'integer';
                break;
            case 'char':
            case 'text':
            case 'mediumText':
            case 'longText':
                $this->validationRules[$name][] = 'string';
                break;
            case 'jsonb':
                $this->validationRules[$name][] = 'json';
                break;
        }
    }
    
    /**
     * Finds validation rules for options part of schema
     * @param array $options
     * @return void
     */
    protected function optionsToValidator($name, array $options) {
        foreach ($options as $key => $value) {
            $value = str_replace('\'', '', $value);
            if ($key == 'unique' && $value) $this->validationRules[$name][] = sprintf('unique:%s', $this->tableName);
            if ($key == 'on') $this->validationRules[$name][] = sprintf('exists:%s,id', $value);
            if ($key == 'nullable' && $value) $this->validationRules[$name] = array_except($this->validationRules[$name], 'required');
            if ($key == 'unsigned' && $value) $this->validationRules[$name][] = 'min:0';
        }
    }
}
