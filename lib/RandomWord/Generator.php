<?php

namespace Press\BusinessLogic\RandomWord;

use JetBrains\PhpStorm\Pure;
use Press\BusinessLogic\RandomWord\Exception\NoResultException;
use Press\BusinessLogic\RandomWord\Exception\OutOfScopeException;
use Throwable;
use Exception;
use Generator as PhpGenerator;
use Press\BusinessLogic\RandomWord\Exception\NoMoreCombinationsException;

/**
 * Class Generator
 * @package Press\BusinessLogic\RandomWord
 */
class Generator
{
    protected const CONSONANTS = 'aeiou';
    protected const VOWELS = 'bcdfghjklmnpqrstvwxyz';
    private const PREFIX = 'cv';

    private string $abc;
    private string $abcMap;
    private int $abcLen;
    private int $cLen;
    private int $vLen;

    private array $consonantsArray;
    private array $vowelsArray;
    private ?array $patternCandidates = null;
    private array $exhaustedPatterns = [];
    private array $stack = [];

    /**
     * Generator constructor.
     * @param int $min
     * @param int $max
     * @param int $count
     * @param bool $balanced
     * @param string $consonants
     * @param string $vowels
     */
    #[Pure] public function __construct(
        private int $min = 2,
        private int $max = 21,
        private int $count = 50,
        private bool $balanced = true,
        private string $consonants = self::CONSONANTS,
        private string $vowels = self::VOWELS
    )
    {
        $this->abc = $this->consonants . $this->vowels;
        $this->abcLen = strlen($this->abc);
        $this->cLen = strlen($this->consonants);
        $this->vLen = strlen($this->vowels);

        $this->abcMap = str_shuffle(str_repeat('c', $this->cLen) . str_repeat('v', $this->vLen));

        $this->consonantsArray = str_split($this->consonants);
        $this->vowelsArray = str_split($this->vowels);
    }

    /**
     * @return PhpGenerator
     * @throws Exception
     */
    public function generate(): PhpGenerator
    {
        for ($i = 0; $i < $this->count; ++$i) {
            $length = random_int($this->min, $this->max);

            $pattern = $this->generatePattern($length);
            $word = $this->generateWordByPattern($pattern);
            yield $word;
        }
    }

    /**
     * @param int $length
     * @return string
     */
    public function generatePattern(int $length): string
    {
        $pattern = self::PREFIX;

        for ($i = 2; $i < $length; ++$i) {
            $pattern .= str_ends_with($pattern, 'cc') ? 'v' : str_shuffle($this->balanced ? $this->abcMap : self::PREFIX)[0];
        }

        return $pattern;
    }

    /**
     * @return string
     */
    protected function randomAbc(): string
    {
        return $this->getRandomChar($this->abc, $this->abcLen);
    }

    /**
     * @return string
     */
    protected function randomConsonants(): string
    {
        return $this->getRandomChar($this->consonants, $this->cLen);
    }

    /**
     * @return string
     */
    protected function randomVowels(): string
    {
        return $this->getRandomChar($this->vowels, $this->vLen);
    }

    /**
     * @param string $text
     * @param int $len
     * @return string
     */
    protected function getRandomChar(string $text, int $len): string
    {
        $i = 0;

        try {
            $i = random_int(1, $len) - 1;
        } catch (Throwable) {
        }

        return $text[$i];
    }

    /**
     * @param string $word
     * @return bool
     */
    public function detectCollision(string $word): bool
    {
        return isset($this->stack[$word]);
    }

    /**
     * @param string $pattern
     * @param int $index
     * @return string[]
     */
    public function getUsedCharsForPattern(string $pattern, int $index): array
    {
        return
            array_map(
                static fn($word) => $word[$index],
                array_keys(
                    array_filter($this->stack, static fn(string $x) => $x === $pattern)
                )
            );
    }

    /**
     * @param string $pattern
     * @param int $index
     * @return array
     */
    public function getStatsForPattern(string $pattern, int $index): array
    {
        return array_count_values($this->getUsedCharsForPattern($pattern, $index));
    }

    /**
     * @param string $pattern
     * @return bool
     */
    #[Pure] public function isExhausted(string $pattern): bool
    {
        return in_array($pattern, $this->exhaustedPatterns, true);
    }

    /**
     * @param string $pattern
     * @return int
     */
    public function calculateVariations(string $pattern): int
    {
        $stat = count_chars($pattern, 1);
        $statC = $stat[ord('c')];
        $statV = $stat[ord('v')];

        return ($this->cLen ** $statC) * ($this->vLen ** $statV);
    }

    /**
     * @param $word
     * @param $pattern
     * @param int $add
     * @return string
     */
    public function incrementExpression($word, $pattern, int $add = 1): string
    {
        $len = strlen($pattern) - 1;
        $newWord = $word;

        for ($i = $len; $add > 0 && $i >= 0; $add = $q, --$i) {
            $type = $pattern[$i];
            $sign = $newWord[$i];
            $collection = $this->getCollectionById($type);
            $pos = strpos($collection, $sign);
            [$q, $r] = gmp_div_qr($add + $pos, strlen($collection));
            $q = (int)$q;
            $r = (int)$r;
            $newWord[$i] = $collection[$r];
        }

        if (0 < $add) {
            throw new OutOfScopeException("");
        }

        return $newWord;
    }

    /**
     * @param string $word
     * @param string $pattern
     * @return string[]
     * @throws Exception
     */
    public function tryFixCollision(string $word, string &$pattern): array
    {
        if (!$this->isExhausted($pattern)) {
            try {
                return $this->useUnusedChars($word, $pattern);
            } catch (NoResultException) {
            }
            try {
                return $this->tryUseAllCombination($word, $pattern);
            } catch (NoResultException) {
            }
            $this->excludePattern($pattern);
        }

        try {
            return $this->generateWithUnusedPattern($pattern);
        } catch (NoResultException) {
        }

        try {
            return $this->useNotExhaustedPattern();
        } catch (NoResultException) {
        }
        throw new NoMoreCombinationsException("No more words can be generated using parameters ...");
    }

    /**
     * @return string[]
     * @throws Exception
     */
    protected function useNotExhaustedPattern(): array
    {
        $diffs = array_diff($this->stack, $this->exhaustedPatterns);
        $pattern = array_shift($diffs);

        if (null !== $pattern) {
            return [$this->generateWordByPattern($pattern), $pattern];
        }

        throw new NoResultException('"');
    }

    /**
     * @param string $pattern
     */
    protected function excludePattern(string $pattern): void
    {
        $this->exhaustedPatterns[] = $pattern;
    }

    /**
     * @param string $pattern
     * @return array
     * @throws Exception
     */
    protected function generateWithUnusedPattern(string &$pattern): array
    {
        while ($this->isExhausted($pattern)) {
            $pattern = $this->grabCandidatePattern();
        }

        return [$this->generateWordByPattern($pattern), $pattern];
    }

    /**
     * @param string $word
     * @param string $pattern
     * @return string[]
     */
    protected function useUnusedChars(string $word, string $pattern): array
    {
        $pLen = strlen($pattern);

        $uniqueness = range(0, $pLen - 1);
        shuffle($uniqueness);

        while (null !== ($i = array_pop($uniqueness))) {
            $collection = $this->getCollectionArrayById($pattern[$i]);
            $usedChars = $this->getUsedCharsForPattern($pattern, $i);
            $res = array_diff($collection, $usedChars);

            if (!empty($res)) {
                $word[$i] = $res[array_rand($res)];
                return [$word, $pattern];
            }
        }
        throw new NoResultException('"');
    }

    /**
     * @param string $word
     * @param string $pattern
     * @return string[]
     */
    protected function tryUseAllCombination(string $word, string $pattern): array
    {
        $variation = $this->calculateVariations($pattern);

        for ($i = 1; $i < $variation; ++$i) {
            try {
                $word = $this->incrementExpression($word, $pattern);
            } catch (OutOfScopeException) {
                $word = $this->createInitWord($pattern);
            }

            if (!$this->detectCollision($word)) {
                return [$word, $pattern];
            }
        }

        throw new NoResultException('"');
    }

    /**
     * @param string $pattern
     * @return string
     */
    protected function createInitWord(string $pattern): string
    {
        $word = '';
        $pLen = strlen($pattern);
        for ($i = 0; $i < $pLen; ++$i) {
            $type = $pattern[$i];
            $word .= $this->getCollectionById($type)[0];
        }

        return $word;
    }

    /**
     * @param $pattern
     * @return string
     * @throws Exception
     */
    protected function generateWordByPattern(&$pattern): string
    {
        $pLen = strlen($pattern);
        $word = '';

        for ($i = 0; $i < $pLen; ++$i) {
            $word .= 'c' === $pattern[$i] ? $this->randomConsonants() : $this->randomVowels();
        }

        if ($this->detectCollision($word)) {
            [$word, $pattern] = $this->tryFixCollision($word, $pattern);
        }

        $this->stack[$word] = $pattern;

        return $word;
    }

    /**
     * @return string
     */
    protected function grabCandidatePattern(): string
    {
        $min = $this->min-2;
        $max = $this->max-2;

        if (!is_array($this->patternCandidates)) {
            $this->patternCandidates = [];
            for($i = $min; $i <= $max; ++$i){
                if( $i > 0 ){
                    $this->patternCandidates[$i] = range(0, (1 << $i) - 1);
                    shuffle($this->patternCandidates[$i]);
                }
            }
        }

        while( !empty($this->patternCandidates) ){
            $sLen = array_rand($this->patternCandidates);
            while (null !== ($i = array_pop($this->patternCandidates[$sLen]))) {
                $candidate = str_pad(decbin($i), $sLen, '0', STR_PAD_LEFT);
                if (!str_contains($candidate, '111')) {
                    return self::PREFIX . str_replace(['1', '0'], ['c', 'v'], $candidate);
                }
            }

            unset($this->patternCandidates[$sLen]);
        }

        throw new NoResultException("");
    }

    /**
     * @param string $char
     * @return string
     */
    protected function getCollectionById(string $char): string
    {
        return 'c' === $char ? $this->consonants : $this->vowels;
    }

    /**
     * @param string $char
     * @return string[]
     */
    protected function getCollectionArrayById(string $char): array
    {
        return 'c' === $char ? $this->consonantsArray : $this->vowelsArray;
    }

    /**
     * @param string $char
     * @return int
     */
    protected function getCollectionCountById(string $char): int
    {
        return 'c' === $char ? $this->cLen : $this->vLen;
    }
}
