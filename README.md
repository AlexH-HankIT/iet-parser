# iet-parser
[![Build Status](https://api.travis-ci.org/MrCrankHank/iet-parser.svg)](https://api.travis-ci.org/MrCrankHank/iet-parser.svg)
[![Total Downloads](https://poser.pugx.org/mrcrankhank/iet-parser/downloads)](https://packagist.org/packages/mrcrankhank/iet-parser)
[![Latest Stable Version](https://poser.pugx.org/mrcrankhank/iet-parser/v/stable)](https://packagist.org/packages/mrcrankhank/iet-parser)
[![Latest Unstable Version](https://poser.pugx.org/mrcrankhank/iet-parser/v/unstable)](https://packagist.org/packages/mrcrankhank/iet-parser)
[![License](https://poser.pugx.org/mrcrankhank/iet-parser/license)](https://packagist.org/packages/mrcrankhank/iet-parser)

### Description
This is a parser for the config and proc files used by the iscsi enterprise target.
It's fully functional e.g. add targets, add acls and stuff like that.

### Api documentation
https://mrcrankhank.github.io/iet-parser/

### Packagist
https://packagist.org/packages/mrcrankhank/iet-parser

### Some notes about the parser:
* Comments starting with # are preserved, but inline comments will be removed.
* Multi line definitions will be merged into one line.
* Empty lines might be removed.

### Testing
The project contains multiple tests, which can be executed via phpunit.

### Links
* https://manpages.debian.org/cgi-bin/man.cgi?sektion=5&query=ietd.conf&apropos=0&manpath=sid&locale=en
* https://sourceforge.net/projects/iscsitarget/

### Some random notes for the author:
* phpcs src --standard=ruleset.xml -s
* Single test: phpunit --filter "/::testDeleteOption$/" .\tests\TargetParserAdd
