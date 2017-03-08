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
random(sum(1, random("hello world")))   // Jaslang runtime exception! Invalid argument at position 0. Expected "number", got hello world
                                        // #0 > random(sum(1, random("hello world")))
                                        // #1 > sum(1, random("hello world"))
                                        // #2 > random("hello world")
                                        
# Variables
let pi : number = 3.142;
let radius : number = 5;

2 * pi * radius                         // 31.42

# Type safety
let foo : number = "bar"                  // Jaslang runtime exception! Assignment expected value of type number, but got "bar"
                                             #0 > let foo : number = "bar"

# Conditionals & loops (factorial)
let n : number = 4;
let total : number = 0;

while (!(n === 0)) {
    if (total === 0) {
        total = 1;
    }
    
    if (!(total === 0)) {
        total = (total * n);
    }
    
    n = (n - 1);
}

total                                   // 24

# User-defined functions (lambdas)
let printit : lambda = (what : string) => {
    print(what)
}

let callit : lambda = (fn : lambda, with : string) => {
    fn(with)
}

callit(printit, 'hello world')          // 'hello world'
```

These examples can be found in [the examples directory](examples/jaslang).

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

* Clean up block/root/statement node concepts, and clean up parser code.
* Ternary operator?
* User functions. Support for chaining operators, and revisiting arg list. Remove necessity for parser to differ
between function calls and paren-grouping.
* Return types
* Doc-generation tool.
* AST dumps, allowing Jaslang (or other) interpreters to be written in other languages. PHP does the "compile" phase
