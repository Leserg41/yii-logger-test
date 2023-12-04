<?php

namespace app\modules;

use yii\log\Logger as YiiLogger;
use yii\log\Target;
use yii\log\FileTarget;
use yii\log\DbTarget;
use yii\log\EmailTarget;
use app\modules\interfaces\LoggerInterface;

/**
 * logger module definition class
 */
class Logger extends \yii\base\Module implements LoggerInterface
{
    const FILE_TYPE = 'file';
    const DB_TYPE = 'db';
    const EMAIL_TYPE = 'email';

    private string $type;
    private Target $target;

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\controllers';

    public function __construct($config = [])
    {
        $this->type = isset($config['type']) ? $config['type'] : self::FILE_TYPE;
        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->setType($this->type);
    }

    public function send(string $message): void
    {
        $this->target->init();
        $this->target->collect([$this->messageData($message)], false);
        $this->target->export();
    }

    public function sendByLogger(string $message, string $loggerType): void
    {
        $savedType = $this->type;
        $this->setType($loggerType);

        $this->target->init();
        $this->target->collect([$this->messageData($message)], false);
        $this->target->export();

        $this->setType($savedType);
    }

    private function messageData (string &$message): array
    {
        return [
            $message, 
            YiiLogger::LEVEL_INFO, 
            'logger-test', 
            microtime(true), 
            [], 
            memory_get_usage()
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
        
        if ($type == self::FILE_TYPE) 
        {
            $this->target = new FileTarget();
        }

        if ($type == self::DB_TYPE) 
        {
            $this->target = new DbTarget();
        }

        if ($type == self::EMAIL_TYPE) 
        {
            $this->target = new EmailTarget();
        }
    }
}
