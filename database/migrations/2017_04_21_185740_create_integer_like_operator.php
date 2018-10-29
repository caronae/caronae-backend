<?php

use Illuminate\Database\Migrations\Migration;

class CreateIntegerLikeOperator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection()->getDriverName() != 'pgsql') {
            return;
        }

        DB::connection()->getPdo()->exec('
            CREATE OR REPLACE FUNCTION public.int_like(leftop integer, rightop text)
             RETURNS BOOLEAN
             LANGUAGE SQL
            AS $function$
            SELECT $1::text LIKE $2;
            $function$;

            CREATE OPERATOR public.~~ (LEFTARG=integer, RIGHTARG=text, PROCEDURE=int_like);
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getDriverName() != 'pgsql') {
            return;
        }

        DB::connection()->getPdo()->exec('
            DROP OPERATOR IF EXISTS public.~~ (integer, text);
            DROP FUNCTION IF EXISTS public.int_like(integer, text);
        ');
    }
}
