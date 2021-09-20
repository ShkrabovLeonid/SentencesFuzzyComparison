<?php

/**
 * FuzzyComparer class
 * use new FuzzyComparer(0.25, 0.45, 3, 2);
*/

class FuzzyComparer{

    private $ThresholdSentence = null;
    private $ThresholdWord = null;
    private $MinWordLength = null;
    private $SubtokenLength = null;

    public function __construct($aThresholdSentence = 0.25, $aThresholdWord = 0.45, $aMinWordLength = 3, $aSubtokenLength = 2){
        $this->ThresholdSentence = $aThresholdSentence;
        $this->ThresholdWord = $aThresholdWord;
        $this->MinWordLength = $aMinWordLength;
        $this->SubtokenLength = $aSubtokenLength;
    }

    public function CalculateFuzzyEqualValue($first, $second) {
        if (empty($first) && empty($second)) {
            return 1.0;
        }

        if (empty($first) || empty($second)) {
            return 0.0;
        };

        $normalizedFirst = $this->NormalizeSentence($first);
        $normalizedSecond = $this->NormalizeSentence($second);

        $tokensFirst = $this->GetTokens($normalizedFirst);
        $tokensSecond = $this->GetTokens($normalizedSecond);

        $fuzzyEqualsTokens = $this->GetFuzzyEqualsTokens($tokensFirst, $tokensSecond);

        $equalsCount = count($fuzzyEqualsTokens);
        $firstCount = count($tokensFirst);
        $secondCount = count($tokensSecond);

        $resultValue = (1.0 * $equalsCount) / ($firstCount + $secondCount - $equalsCount);

        return $resultValue;
    }

    private function GetFuzzyEqualsTokens($tokensFirst, $tokensSecond) {
        $equalsTokens = array();
        $usedToken = array();
        for ($i = 0; $i < count($tokensFirst); ++$i) {
            for ($j = 0; $j < count($tokensSecond); ++$j) {
                if ( !isset($usedToken[$j]) ) {
                    sleep(0.5);
                    if ($this->IsTokensFuzzyEqual($tokensFirst[$i], $tokensSecond[$j])) {
                        $equalsTokens[] = $tokensFirst[$i];
                        $usedToken[$j] = true;
                        break;
                    }
                }
            }
        }

        return $equalsTokens;
    }

    private function IsTokensFuzzyEqual($firstToken, $secondToken) {
        $equalSubtokensCount = 0;
        $usedTokens = array();
        for ($i = 0; $i < strlen($firstToken) - $this->SubtokenLength + 1; ++$i) {
            $subtokenFirst = mb_strimwidth($firstToken, $i, $this->SubtokenLength);
            for ($j = 0; $j < strlen($secondToken) - $this->SubtokenLength + 1; ++$j) {
                if ( isset(!$usedTokens[$j]) ) {
                    $subtokenSecond = mb_strimwidth($secondToken, $j, $this->SubtokenLength);
                    if ($subtokenFirst === $subtokenSecond) {
                        $equalSubtokensCount++;
                        $usedTokens[$j] = true;
                        break;
                    }
                }                    
            }
        }

        $subtokenFirstCount = strlen($firstToken) - $this->SubtokenLength + 1;
        $subtokenSecondCount = strlen($secondToken) - $this->SubtokenLength + 1;

        $tanimoto = (1.0 * $equalSubtokensCount) / ($subtokenFirstCount + $subtokenSecondCount - $equalSubtokensCount);

        return $this->ThresholdWord <= $tanimoto;
    }

    private function GetTokens($sentence) {
        $tokens = array();
        $words = explode(" ", $sentence);
        foreach ($words as $key => $word) {
            if (strlen($word)  >= $this->MinWordLength) {
                $tokens[] = $word;
            }
        }

        return $tokens;
    }
    
    private function NormalizeSentence($sentence) {
        $resultContainer = array();
        $lowerSentece = mb_strtolower($sentence);
        $lowerSentece = $this->str_split_unicode($lowerSentece);
        $normalSentece = preg_replace('/[^ a-zа-яё\d[.]/ui', '', $lowerSentece);
        foreach ($normalSentece as $key => $c) {
            $resultContainer[] = $c;
        }
        
        return implode($resultContainer);
    }

    public function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

}