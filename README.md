# Jaslang

Jaslang is split in to two parts: the engine and the core language.

* The engine is a framework for defining & implementing a language.
* The core language is a programming language built using the engine.

These two parts of the project are contained in this repository, but may
be separated in the future. Written in PHP.

## Jaslang Engine

A lexer, parser and evaluator. Development of the engine is driven
by requirements of the core language. Whilst this imposes some constraints
on the kinds of languages that can be defined, the engine aims to be
abstracted and extensible enough to define languages beyond Jaslang.
It comprises of:

* A language parser with distinct lexical and AST-phases.
* Debugging information, including syntax checks and evaluation traces.
* Pluggable system for types, functions and operators.

## Jaslang Language

A language designed around the evaluation of simple expressions.

The output of a Jaslang expression is a single string. Jaslang supports 
expressions of arbitrary complexity. Currently supported language 
features:

* Simple type system: Number, String and Boolean.
* Multiple statements, program output is based on the final statement.

### Examples

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
                                        
# Variables
let number pi = 3.142;
let number radius = 5;

2 * pi * radius                         // 31.42

# Type safety
let number foo = "bar"                  // Jaslang runtime exception! Assignment expected value of type number, but got "bar"
                                           #0 > let number foo = "bar"
```

The list of core functions is currently very limited due to focus on the engine.

# TODO

* Test foundation for simple, separate test classes (e.g. loading prebuilt Jaslang evaluators & loading .jsl files?)
* Passing references to functions would allow for non-engine implementation of increment operator, for e.g.
* Ternary operator?
* Clean up phar/build
* Return types
* Doc-generation tool.
* AST dumps, allowing Jaslang (or other) interpreters to be written in other languages. PHP does the "compile" phase
