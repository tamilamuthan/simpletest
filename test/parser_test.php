<?php
    // $Id$
    
    if (!defined("SIMPLE_TEST")) {
        define("SIMPLE_TEST", "../");
    }
    require_once(SIMPLE_TEST . 'parser.php');
    Mock::generate("HtmlPage");
    Mock::generate("TokenHandler");

    class TestOfCompoundRegex extends UnitTestCase {
        function TestOfCompoundRegex() {
            $this->UnitTestCase();
        }
        function testNoPatterns() {
            $regex = &new CompoundRegex();
            $this->assertFalse($regex->match("Hello", $match));
            $this->assertEqual($match, "");
        }
        function testNoSubject() {
            $regex = &new CompoundRegex();
            $regex->addPattern(".*");
            $this->assertTrue($regex->match("", $match));
            $this->assertEqual($match, "");
        }
        function testMatchAll() {
            $regex = &new CompoundRegex();
            $regex->addPattern(".*");
            $this->assertTrue($regex->match("Hello", $match));
            $this->assertEqual($match, "Hello");
        }
    }

    class TestOfLexer extends UnitTestCase {
        function TestOfLexer() {
            $this->UnitTestCase();
        }
        function testEmptyPage() {
            $handler = &new MockTokenHandler($this);
            $handler->expectMaximumCallCount("acceptToken", 0);
            $handler->setReturnValue("acceptToken", true);
            $handler->expectMaximumCallCount("acceptUnparsed", 0);
            $handler->setReturnValue("acceptUnparsed", true);
            $lexer = &new SimpleLexer($handler);
            $this->assertTrue($lexer->parse(""));
        }
        function testNoPatterns() {
            $handler = &new MockTokenHandler($this);
            $handler->expectMaximumCallCount("acceptToken", 0);
            $handler->setReturnValue("acceptToken", true);
            $handler->expectArgumentsSequence(0, "acceptUnparsed", array("abcdef"));
            $handler->expectCallCount("acceptUnparsed", 1);
            $handler->setReturnValue("acceptUnparsed", true);
            $lexer = &new SimpleLexer($handler);
            $this->assertTrue($lexer->parse("abcdef"));
            $handler->tally();
        }
        function testSinglePattern() {
            $handler = &new MockTokenHandler($this);
            $handler->expectArgumentsSequence(0, "acceptToken", array("aaa"));
            $handler->expectArgumentsSequence(1, "acceptToken", array("a"));
            $handler->expectArgumentsSequence(2, "acceptToken", array("a"));
            $handler->expectArgumentsSequence(3, "acceptToken", array("aaa"));
            $handler->expectCallCount("acceptToken", 4);
            $handler->setReturnValue("acceptToken", true);
            $handler->expectArgumentsSequence(0, "acceptUnparsed", array("x"));
            $handler->expectArgumentsSequence(1, "acceptUnparsed", array("yyy"));
            $handler->expectArgumentsSequence(2, "acceptUnparsed", array("x"));
            $handler->expectArgumentsSequence(3, "acceptUnparsed", array("z"));
            $handler->expectCallCount("acceptUnparsed", 4);
            $handler->setReturnValue("acceptUnparsed", true);
            $lexer = &new SimpleLexer($handler);
            $lexer->addPattern("a+");
            $this->assertTrue($lexer->parse("aaaxayyyaxaaaz"));
            $handler->tally();
        }
        function testMultiplePattern() {
            $handler = &new MockTokenHandler($this);
            $target = array("a", "b", "a", "bb", "b", "a", "a");
            for ($i = 0; $i < count($target); $i++) {
                $handler->expectArgumentsSequence($i, "acceptToken", array($target[$i]));
            }
            $handler->expectCallCount("acceptToken", count($target));
            $handler->setReturnValue("acceptToken", true);
            $handler->expectArgumentsSequence(0, "acceptUnparsed", array("x"));
            $handler->expectArgumentsSequence(1, "acceptUnparsed", array("xxxxxx"));
            $handler->expectArgumentsSequence(2, "acceptUnparsed", array("x"));
            $handler->expectCallCount("acceptUnparsed", 3);
            $handler->setReturnValue("acceptUnparsed", true);
            $lexer = &new SimpleLexer($handler);
            $lexer->addPattern("a+");
            $lexer->addPattern("b+");
            $this->assertTrue($lexer->parse("ababbxbaxxxxxxax"));
            $handler->tally();
        }
    }

    class TestOfLexerModes extends UnitTestCase {
        function TestOfLexerModes() {
            $this->UnitTestCase();
        }
        function testIsolatedPattern() {
            $handler = &new MockTokenHandler($this);
            $handler->expectArgumentsSequence(0, "acceptToken", array("a"));
            $handler->expectArgumentsSequence(1, "acceptToken", array("aa"));
            $handler->expectArgumentsSequence(2, "acceptToken", array("aaa"));
            $handler->expectArgumentsSequence(3, "acceptToken", array("aaaa"));
            $handler->expectCallCount("acceptToken", 4);
            $handler->setReturnValue("acceptToken", true);
            $handler->setReturnValue("acceptUnparsed", true);
            $lexer = &new SimpleLexer($handler, "used");
            $lexer->addPattern("a+", "used");
            $lexer->addPattern("b+", "unused");
            $this->assertTrue($lexer->parse("abaabxbaaaxaaaax"));
            $handler->tally();
        }
    }
    
    class TestOfParser extends UnitTestCase {
        function TestOfParser() {
            $this->UnitTestCase();
        }
        function testEmptyPage() {
            $page = &new MockHtmlPage($this);
            $page->expectCallCount("addLink", 0);
            $page->expectCallCount("addFormElement", 0);
            $parser = &new HtmlParser();
            $this->assertTrue($parser->parse("", $page));
            $page->tally();
        }
    }
?>