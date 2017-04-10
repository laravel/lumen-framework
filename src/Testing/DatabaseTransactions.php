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
            $db = $this->app->make('db');
            $db->rollBack();
            $db->disconnect();
        });
    }
}
