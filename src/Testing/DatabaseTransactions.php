<?php

namespace Laravel\Lumen\Testing;

trait DatabaseTransactions
{
    /**
     * Begin a database transaction.
     *
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        $this->app->make('db')->beginTransaction();

        $this->beforeApplicationDestroyed(function () {
            $this->app->make('db')->rollBack();
        });
    }
}
