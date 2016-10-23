# Jaslang

A language capable of parsing and evaluating simple expressions. Written in PHP.

The output of a Jaslang expression is a single string. Jaslang supports expressions of arbitrary complexity.
Currently supported language features:

* Functions, including support for register user-defined functions.
* Binary operators, including support for registering user-defined operators.
* Simple type system: Number and String.
* A parse engine built with distinct lexical and AST-phases.
* Debugging information, including syntax checks and evaluation traces.

## TODO

* Booleans
* Ternary operator
* Clean up phar/build
* Return types
* Operator precedence.