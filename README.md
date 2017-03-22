# Jaslang

Jaslang is split in to two parts: the engine and the core language.

* The core language is a programming language built using the engine.
* The engine is a framework for defining & implementing a language.

These two parts of the project are contained in this repository, but may
be separated in the future. Written in PHP.

## Jaslang Language

A simple imperative programming language.

* Simple type system: Number, String and Boolean,
* Strongly typed variables,
* Conditionals (if),
* Loops (while),
* Syntax and runtime error checking.
* Functions (first-class)

The list of core functions and operators is currently very limited due to focus on the engine.

### Try it

Get a copy of the Jaslang phar from the releases page.

Write a JSL file:
```bash
$ echo "println('hello world')" > test.jsl
```

Make the phar executable and run it:
```bash
$ chmod 755 jaslang.phar
$ ./jaslang.phar test.jsl
```

Or run the phar via PHP:
```bash
$ php jaslang.phar test.jsl
```

### Examples

Available on [the wiki](https://github.com/ehimen/jaslang/wiki/Examples).

## Jaslang Engine

A lexer, parser and evaluator. Development of the engine is driven
by requirements of the core language. Whilst this imposes some constraints
on the kinds of languages that can be defined, the engine aims to be
abstracted and extensible enough to define languages beyond Jaslang.
It comprises of:

* A language parser with distinct lexical and AST-phases.
* Debugging information, including syntax checks and evaluation traces.
* Pluggable system for types, functions and operators.

# TODO

* Arrays/iterable/tuples/structs.
* Clean up block/root/statement node concepts, and clean up parser code.
* Ternary operator?
* Remove necessity for parser to differ between function calls and paren-grouping.
* Return types
* Doc-generation tool.
* AST dumps, allowing Jaslang (or other) interpreters to be written in other languages. PHP does the "compile" phase.
