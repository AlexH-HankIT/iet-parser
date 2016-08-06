### Parser comments
* Preserve comments starting with # (But not in the middle of a line, only at the beginning)
* Multi line definitions with \ are not supported
* Empty lines might be removed

### PHP sniffer
* phpcs src --standard=ruleset.xml -s

### PHPUnit
* Single test: phpunit --filter "/::testDeleteOption$/" .\tests\TargetParserAdd

### Links
* https://manpages.debian.org/cgi-bin/man.cgi?sektion=5&query=ietd.conf&apropos=0&manpath=sid&locale=en

### ToDo: 
* AclParser
    * add (add a acl to a new or existing target inside the allow file)
    * delete (+ delete target definition if acl is last)
    * get
    
* ProcParser
    * getSession (+ param for single one)
    * getVolumes (+ param for single one)