<?php

namespace App\BaseMigrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class MigrationBase extends Migration
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = null;

    /**
     * Run the migrations.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->model::getModelTable());
    }

    /**
     * 填充数据
     *
     * @return void
     */
    protected function seed()
    {
        if ($this->seeder) {
            DB::beginTransaction();
            $this->seeder::seed();
            DB::commit();

            if (method_exists($this->seeder, 'afterSeeding')) {
                $this->seeder::afterSeeding();
            }
        }
    }
}