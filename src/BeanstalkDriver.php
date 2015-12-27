<?php

/**
 * This file is part of the Queue package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Queue;

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Queue\Driver\DriverInterface;
use Queue\Job\JobInterface;
use Queue\Serializer\JobSerializer;

class BeanstalkDriver implements DriverInterface
{
    /**
     * @var PheanstalkInterface
     */
    private $pheanstalk;

    /**
     * @var JobSerializer
     */
    private $serializer;

    /**
     * BeanstalkDriver constructor.
     *
     * @param PheanstalkInterface $pheanstalk
     */
    public function __construct(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
        $this->serializer = new JobSerializer();
    }

    public function addJob(JobInterface $job)
    {
        $this->pheanstalk
            ->put($this->serializer->serialize($job));
    }

    public function resolveJob()
    {
        $job = $this->pheanstalk
            ->reserve();

        return $this->serializer->unserialize($job);
    }

    public function removeJob(JobInterface $job)
    {
        $this->pheanstalk->delete(new Job($job->getData()['_beanstalk_id'], []));
    }

    public function buryJob(JobInterface $job)
    {
        $this->pheanstalk->bury(new Job($job->getData()['_beanstalk_id'], []));
    }
}
