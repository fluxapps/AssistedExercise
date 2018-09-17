ILIAS-Plugin Assisted Exercise

###Installation
Start at your ILIAS root directory

1. mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject/ 
2. cd  Customizing/global/plugins/Services/Repository/RepositoryObject/
3. git clone https://github.com/studer-raimann/AssistedExercise.git  
4. As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.
This Plugin requires ActiveRecord.


###roll configuration
After the Installation you have to set the following roll configurations:
1. Globale role user in the section assisted exercise: 

    a) visiable and read
2. Role template course member in the section assisted exercise: 

    a) visiable and read
3. Role template course administrator: 

    a) in the section assisted exercise:
    
        - all rights
    b) in the section folders:
    
        - create assisted exercise




### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Source Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.


### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  
