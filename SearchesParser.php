<?php

class SearchesParser
{
    private $filename;

    private $words = [];

    public function __construct($filename)
    {
        if (!is_readable($filename)) {
            throw new Exception('File not found');
        }
        
        $this->filename = $filename;
    }

    public function parse()
    {
        $fileHandler = fopen($this->filename, 'r');

        // Skip headers
        fgetcsv($fileHandler);

        while ($line = fgetcsv($fileHandler)) {
            $data = $this->parseLine($line);
            $this->saveData($data);
        }

        fclose($fileHandler);
    }

    private function parseLine($line)
    {
        if (!isset($line[1])) {
            throw new Exception('Line format broken');
        }

        list($word, $searches) = $line;

        if (preg_match('#^\[(.+)\]$#', $word, $matches)) {
            $word = $matches[1];
        }

        return [
            'words' => explode(' ', $word),
            'searches' => $searches,
            'isExact' => !!$matches,
        ];
    }

    private function saveData($data)
    {
        foreach ($data['words'] as $word) {
            $this->addWord($word);
            if ($data['isExact']) {
                $this->calculateExact($word, $data['searches']);
            } else {
                $this->calculateBroad($word, $data['searches']);
            }
        }
    }

    private function addWord($word)
    {
        if (isset($this->words[$word])) {
            $this->words[$word]['counter'] ++;
        } else {
            $this->words[$word] = [
                'counter' => 1,
                'broad' => 0,
                'exact' => 0,
            ];
        }
    }

    private function calculateExact($word, $searches)
    {
        $this->words[$word]['exact'] += $searches;
    }

    private function calculateBroad($word, $searches)
    {
        $this->words[$word]['broad'] += $searches;
    }

    private function getHader()
    {
        return 'Word,Count,Total Broad Searches,Total Exact Searches';
    }

    private function getWords()
    {
        return $this->words;
    }

    public function renderResult()
    {
        echo $this->getHader();

        foreach ($this->getWords() as $word => $data) {
            printf("%s,%d,%d,%d\n", $word, $data['counter'], $data['broad'], $data['exact']);
        }
    }
}

try {
    $searches = new SearchesParser('searches.csv');
    $searches->parse();

    $searches->renderResult();
} catch (Exception $e) {
    printf("Error on line %d with message: %s \n", $e->getLine(), $e->getMessage());
}
