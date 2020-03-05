<?php

namespace SentimentClassifier\Helper;

use FilesystemIterator;
use Phpml\Dataset\ArrayDataset;
use SentimentClassifier\Base\Base;
use SentimentClassifier\Exception\DataHandlerException;

class DataHandler extends Base
{
    const DEFAULT_SAMPLE_COUNT_PER_TARGET = 1000;

    private $dataset;

    private $samples = [];
    private $targets = [];
    private $sampleCountPerTarget;

    /**
     * DataHandler constructor.
     * @param null $datasetName
     * @param int $sampleCountPerTarget
     * @param bool $verbose
     * @throws DataHandlerException
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function __construct($datasetName = NULL, $sampleCountPerTarget = self::DEFAULT_SAMPLE_COUNT_PER_TARGET, $verbose = false)
    {
        $this->verbose = $verbose;
        $this->sampleCountPerTarget = $sampleCountPerTarget;

        if($datasetName !== NULL){
            $this->loadDataSet($datasetName);
        }
    }

    /**
     * @param $datasetName
     * @throws DataHandlerException
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    private function loadDataSet($datasetName){

        $this->output("Loading dataset\n\n");

        //read dataset files
        $datasets_path = dirname(__DIR__) . "/../../assets/datasets/" . $datasetName;

        if (!file_exists($datasets_path)){
            throw new DataHandlerException("Could not locate the dataset");
        }

        if(!is_dir($datasets_path)){
            throw  new DataHandlerException("The dataset path is not a directory");
        }

        $iterator = new FilesystemIterator($datasets_path);

        while ($iterator->valid()){

            //verify file type
            if($iterator->getExtension() !== "json"){
                throw new DataHandlerException("Unknown file type at: " . $iterator->getPathname());
            }

            //get the basename without the extension, this will act as a class/target label
            $label = $iterator->getBasename(".json");

            //read the content of the file
            $data = file_get_contents($iterator->getPathname());

            //decode the json array
            $json_data = json_decode($data,true);

            if($json_data === NULL){
                throw new DataHandlerException("Unable to json decode data in file: " . $iterator->getPathname());
            }

            //loop through $json_data
            $sample_count = 0;

            foreach ($json_data as $text){

                if($sample_count >= $this->sampleCountPerTarget){
                    break;
                }

                $this->samples[] = $text;
                $this->targets[] = $label;

                $sample_count++;
            }

            $iterator->next();
        }

        if (count($this->targets) < 2){
            throw new DataHandlerException("A dataset must have more than one class");
        }

        $this->dataset = new ArrayDataset($this->samples,$this->targets);

    }

    /**
     * @return ArrayDataset
     */
    public function getDataset()
    {
        return $this->dataset;
    }

}