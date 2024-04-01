<?php

class Modules_HumHubExtension_Script extends pm_LongTask_Task
{
    public function run()
    {
        $output = [];
        $returnCode = 0;
        exec('humhub-script.sh', $output, $returnCode);

        if ($returnCode === 0) {
            $this->setResult(pm_LongTask_Result::STATUS_OK);
        } else {
            $this->setResult(pm_LongTask_Result::STATUS_ERROR, implode("\n", $output));
        }
    }
}

$task = new Modules_HumHubExtension_Script();
$task->start();
