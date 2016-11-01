# Jaslang

A language capable of parsing and evaluating simple expressions. Written in PHP.

The output of a Jaslang expression is a single string. Jaslang supports expressions of arbitrary complexity.
Currently supported language features:

* Functions, including support for easily creating user-defined functions.
* Binary operators, including support for easily creating user-defined operators.
* Simple type system: Number, String and Boolean.
* A parse engine built with distinct lexical and AST-phases.
* Debugging information, including syntax checks and evaluation traces.

## Examples

```
# Numbers, functions and operators.
sum(1, 3)                               // "4"
subtract(-4.5, -3)                      // "-1.5"
1 + 3 + 2 + 5 - 2                       // "9"

# Boolean & strings
"ello" === substring("hello", 1, 4)     // "true"

# Controlling precendence
3 - 3 + 1                               // "1"
3 - (3 + 1)                             // "-1"

# Syntax checking
sum(1, sum(2, sum(3, 4))                // "Jaslang syntax error! Input: sum(1, sum(2, sum(3, 4))
                                        //  Unexpected end of input"
foo bar                                 // "Jaslang syntax error! Input: foo bar 
                                        //  Unexpected token: bar @5"

# Type validation & stack traces
random("hello world")                   // Jaslang runtime exception! Invalid argument at position 0. Expected "number", got hello world
                                        // #0 > random(sum(1, random("hello world")))
                                        // #1 > sum(1, random("hello world"))
                                        // #2 > random("hello world")
```

The list of core functions is currently very limited due to focus on the engire.

## TODO

* Variables
* Revisit test suite, implementing useful test function/types in PHP resources. Test foundation for simple, separate test classes (e.g. loading prebuilt Jaslang evaluators & loading .jsl files?)
* Following on from test suite revisit, consider removing core types/functions/operators?
* Unary operators (bool negation)
* Passing references to functions would allow for non-engine implementation of increment operator, for e.g.
* Evaluation context: program output, pure functions?
* Ternary operator?
* Clean up phar/build
* Return types
* Default operator precedence.
* Work on string-like type.
* Doc-generation tool.
* AST dumps, allowing Jaslang (or other) interpreters to be written in other languages. PHP does the "compile" phase
* More generic solution to lexer/parser, allowing creation of arbitrary languages
