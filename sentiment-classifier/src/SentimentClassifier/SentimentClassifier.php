<?php

namespace SentimentClassifier;

use Phpml\Classification\NaiveBayes;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Metric\Accuracy;
use Phpml\Tokenization\WordTokenizer;
use SentimentClassifier\Base\Base;
use SentimentClassifier\Exception\SentimentClassifierException;
use SentimentClassifier\Helper\DataHandler;

class SentimentClassifier extends Base
{
    private $dataset;
    private $preprocessedDataset;

    private $rawSamples;

    private $classifier;

    private $trainingStartTime;
    private $trainingCompletionTime;

    private $trained = false;

    private $vectorizer;
    private $tfIdTransformer;

    /**
     * SentimentClassifier constructor.
     * @param $datasetName
     * @param $sampleCountPerTarget
     * @param bool $verbose
     * @throws Exception\DataHandlerException
     * @throws \Phpml\Exception\InvalidArgumentException
     * @throws SentimentClassifierException
     */
    public function __construct($datasetName, $sampleCountPerTarget, $verbose = false)
    {

        ##instantiate class variables
        $this->verbose = $verbose;

        $this->vectorizer = new TokenCountVectorizer(new WordTokenizer());
        $this->tfIdTransformer = new TfIdfTransformer();

        ## load dataset

        $dataHandler = new DataHandler($datasetName,$sampleCountPerTarget,$verbose);
        $this->dataset = $dataHandler->getDataset();
        $samples = $this->dataset->getSamples();

        ## prepare dataset
        $this->output("Preparing dataset...\n");

        $this->prepareSamples($samples);
        $this->preprocessedDataset = new ArrayDataset($samples,$this->dataset->getTargets());

        //generate the training dataset
        $randomSplit = new StratifiedRandomSplit($this->preprocessedDataset,0.1);

        $trainingSamples = $randomSplit->getTrainSamples();
        $trainingLabels = $randomSplit->getTrainLabels();

        $testSamples = $randomSplit->getTestSamples();
        $testLabels = $randomSplit->getTestLabels();

        ## train the classifier
        $this->classifier = new NaiveBayes();

        //get the starting time
        $this->trainingStartTime = time();

        $this->output("Training....\n" . "starting time: " . date("Y-m-d H:i:s", $this->trainingStartTime) . "\n");

        $this->classifier->train($trainingSamples,$trainingLabels);

        $this->trained = true;

        //get training stop time
        $this->trainingCompletionTime = time();
        $duration = $this->trainingCompletionTime - $this->trainingStartTime;

        $this->output(
            "\nTraining completed!\n" .
            "completion time: " . date("Y-m-d H:i:s", $this->trainingCompletionTime) . "\n" .
            "training duration: $duration seconds \n"
        );

        ## predict
        $predicted_labels = $this->predict($testSamples,true);

        ## accuracy
        $this->output("Accuracy: " . Accuracy::score($testLabels,$predicted_labels) . "\n");

    }

    private function prepareSamples(&$samples){

        //vectorize
        $this->vectorizer->fit($samples);
        $this->vectorizer->transform($samples);

        //Tf-idf transformer
        $this->tfIdTransformer->fit($samples);
        $this->tfIdTransformer->transform($samples);

    }

    /**
     * @param $samples
     * @param bool $preprocessed
     * @return array|string
     * @throws SentimentClassifierException
     */
    public function predict($samples, $preprocessed = false){

        if(!$this->trained){
            throw new SentimentClassifierException("Classifier not trained");
        }

        $this->output("Predicting....\n");

        if(!$preprocessed){
            $raw_samples = $this->dataset->getSamples();
            $merged_samples = array_merge($raw_samples,$samples);
            $this->prepareSamples($merged_samples);
            $offset = count($raw_samples);
            $samples = array_slice($merged_samples,$offset);
        }

        return $this->classifier->predict($samples);

    }

    //end of class
}