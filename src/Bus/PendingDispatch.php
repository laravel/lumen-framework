<?php

namespace Laravel\Lumen\Bus;

use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed  $job
     * @return void
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * Set the desired connection for the job.
     *
     * @param  string|null  $connection
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->job->onConnection($connection);

        return $this;
    }

    /**
     * Set the desired queue for the job.
     *
     * @param  string|null  $queue
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->job->onQueue($queue);

        return $this;
    }

    /**
     * Determine if the job should be dispatched.
     *
     * @return bool
     */
    protected function shouldDispatch()
    {
        if (! $this->job instanceof ShouldBeUnique) {
            return true;
        }

        $uniqueId = method_exists($this->job, 'uniqueId')
                    ? $this->job->uniqueId()
                    : ($this->job->uniqueId ?? '');

        $cache = method_exists($this->job, 'uniqueVia')
                    ? $this->job->uniqueVia()
                    : Container::getInstance()->make(Cache::class);

        return (bool) $cache->lock(
            $key = 'laravel_unique_job:'.get_class($this->job).$uniqueId,
            $this->job->uniqueFor ?? 0
        )->get();
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        if (! $this->shouldDispatch()) {
            return;
        } else {
            app(Dispatcher::class)->dispatch($this->job);
        }
    }
}
