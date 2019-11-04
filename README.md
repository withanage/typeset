# OJS Typeset plugin
### Description

Allows integration of a command line tool to execute file conversion  to XML in OJS.

Currently supported:  
* Tools : [meTypeset](https://github.com/MartinPaulEve/meTypeset)
* Formats: DOCX/ODT -> JATS XML

For other command line tool integrations,  minor code is needed.  

### Requirements
* OJS 3.1.2 or later
* [meTypeset](https://github.com/MartinPaulEve/meTypeset) 


### Installation 

### Recommended: tool configuration through admin
```bash
OJS_PATH=DEFINE_YOUR_OJS_PATH
cd $OJS_PATH/plugins/generic
git clone https://github.com/withanage/typeset 

# Add meTypeset in cofig.inc.php under [cli] section
; meTypeet path
meTypeset = /home/withanage/software/meTypset/bin/meTypeset.py

```
#### Settings configuration
```bash
# Add tool path e.g. (usr/local/meTypset/bin/meTypset.py) under  
Settings -> Website -> Plugins -> Installed Plugins -> generic Plugins-> typeset  

```


### Run conversion
![typeset](assets/typeset_run.gif)


### Contact

https://github.com/withanage/typeset/issues


