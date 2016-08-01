https://manpages.debian.org/cgi-bin/man.cgi?sektion=5&query=ietd.conf&apropos=0&manpath=sid&locale=en

* Preserve comments starting with # (But not in the middle of a line, only at the beginning)
* Multi line definitions with \ are not supported
* Empty lines might be removed

## GlobalOptionParser
### Add
#### Basic usage
```php
    $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);

    $filesystem = new Filesystem($local);

    $parser = new GlobalOptionParser($filesystem, 'file.txt');

    $parser->add("test")->write();
```

#### Helper methods:
##### addIncomingUser()
```php
    $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);
    
    $filesystem = new Filesystem($local);
    
    $parser = new GlobalOptionParser($filesystem, 'file.txt');
    
    $parser->addIncomingUser("user", "password")->write();
```

##### addOutgoingUser()
```php
    $local = new Local(__DIR__ . DIRECTORY_SEPARATOR . 'files', LOCK_EX);
    
    $filesystem = new Filesystem($local);
    
    $parser = new GlobalOptionParser($filesystem, 'file.txt');
    
    $parser->addOutgoingUser("user", "password")->write();
```