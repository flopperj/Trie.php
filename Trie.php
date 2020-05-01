<?php

/**
 * Class TrieNode
 */
class TrieNode
{
    var $key = null;
    var $parent = null;
    var $children = [];
    var $end = false;

    /**
     * TrieNode constructor.
     * @param null $key
     */
    public function __construct($key = null)
    {
        $this->key = $key;
    }

    /**
     * Gets current node's word
     * @returns string
     */
    public function getWord()
    {
        // initialize our node to current node
        $node = $this;

        // initialize word to empty string
        $word = '';

        // traverse up all parents till we hit the root node
        while ($node != null) {

            // prepend the key for current node to word
            $word = $node->key . $word;


            // set new node to parent
            $node = $node->parent;

        }

        // return word
        return $word;
    }

}

/**
 * Class Trie
 */
class Trie
{
    var $root = null;

    /**
     * Trie constructor.
     */
    public function __construct()
    {
        $this->root = new TrieNode();
    }

    /**
     * Checks to see if Trie contains a word
     * @param string $word
     * @return bool
     */
    public function contains($word)
    {
        // return false if word is null
        if (!$word) {
            return false;
        }

        // initialize node to root
        $node = $this->root;

        // loop through all characters in word
        $chars = str_split($word);
        for ($i = 0; $i < sizeof($chars); $i++) {

            $char = $chars[$i];

            // check if character exists in node's children
            if (!empty($node->children[$char])) {

                // set node to child so that we can walk up the tree
                $node = $node->children[$char];

            } else {

                // word doesn't exist in Trie
                return false;
            }
        }

        // use node's end flag to make sure our word exists
        // Ex: If we have only John Doe in our trie but are trying to find John, this should return false
        return $node->end;
    }

    /**
     * Inserts a word to trie
     * @param string $word
     */
    public function insert($word)
    {
        // if word empty lets not continue farther
        if (empty($word)) {
            return;
        }

        // initialize our node to root node
        $node = $this->root;

        // loop through all characters in word
        $chars = str_split($word);
        for ($i = 0; $i < sizeof($chars); $i++) {

            $char = $chars[$i];

            // check to make sure character does not exist in node's children
            if (empty($node->children[$char])) {

                // add the character to children as a new node
                $node->children[$char] = new TrieNode($char);

                // child's parent to node
                $node->children[$char]->parent = $node;

            }

            // set node from new child to continue building our branch up to the leaf
            $node = $node->children[$char];

            // if we've reached the end mark node as leaf
            if ($i == sizeof($chars) - 1) {
                // set end flag to true for node
                $node->end = true;
            }
        }
    }

    /**
     * Removes a word from trie
     * @param string $word
     */
    public function remove($word)
    {
        // we only want to deal with non-empty strings
        if (empty($word)) {
            return;
        }

        /**
         * Recursive helper to walk through trie and remove word
         *
         * @param TrieNode $node
         * @param string $wordToRemove
         */
        $removeWord = function ($node, $wordToRemove) use (&$removeWord) {

            // check our node to see if it's at the end.
            // make sure that the node's word matches the one we want to remove
            if ($node->end && $node->getWord() == $wordToRemove) {

                // check for children so that we can reset the end flag to false this way we do not remove words that
                // contain the word to remove
                if (!empty($node->children)) {
                    $node->end = false;
                } else {
                    // find the parent and set children to empty array
                    $node->parent->children = [];
                }

                // break our recursive call since we've removed our word
                return;
            }

            // Traverse down all children of node to find end of word till we no longer have any children
            foreach ($node->children as $child) {
                $removeWord($child, $wordToRemove);
            }
        };

        // call remove word on root node
        $removeWord($this->root, $word);

    }

    /**
     * returns array of all possible words that start with given prefix
     *
     * @param string $prefix
     * @return array
     */
    public function startsWith($prefix)
    {

        // initialize our output array
        $output = [];

        // helper to traverse through tree recursively and find all words that start with given prefix
        // findAll(node, prefix, output[])
        $findAll = function ($node, $prefix) use (&$findAll, &$output) {

            // get current node's word
            $word = $node->getWord();

            // only look at nodes that have reached the end to check if word starts with prefix
            // if prefix is empty we'll get all words in our trie
            if ($node->end && (empty($prefix) || strpos($word, $prefix) === 0 && strlen($word) > 0)) {
                // push word to output
                $output[] = $word;
            }

            // loop through all children of node
            foreach ($node->children as $child) {

                // call findAll(child, prefix, output) so that we can find end of word
                $findAll($child, $prefix, $output);
            }

            return $output;
        };

        // return recursive call on root node
        return $findAll($this->root, $prefix);
    }
}


/*
 TEST CASES
 */
$trie = new Trie();

// Test values
$words = [
    'John',
    'John Doe',
    'Jane Doe',
    'Jimmy',
    'Eric',
    'Billy Joel'
];

// insert and assert that values exist
foreach ($words as $word) {

    // insert word to trie
    $trie->insert($word);

    // test that word is in trie
    assert($trie->contains($word) == true);
}


// Test contains logic
// NOTE: Even though Joel is a substring of "Billy Joel" in Trie, it's not contained because it's not a full word
assert($trie->contains('Joel') == false);

// NOTE: Even though Doe is a substring of "John Doe" and "Jane Doe" in Trie, it's not contained because it's not a full word
assert($trie->contains('Doe') == false);
assert($trie->contains('Eric') == true);

// Test starts with logic
assert($trie->startsWith('Jo') == ['John', 'John Doe']);

// Test that we have all words
assert($trie->startsWith('') == $words);

// remove a word
$trie->remove('John Doe');

// test that it was actually removed
assert($trie->startsWith('') == ['John', 'Jane Doe', 'Jimmy', 'Eric', 'Billy Joel']);