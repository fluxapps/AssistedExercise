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




HINWEIS: Dieses Plugin wird open source durch die studer + raimann ag der ILIAS Community zur Verüfgung gestellt. Das Plugin hat noch keinen Pluginpaten. Das heisst, dass die studer + raimann ag etwaige Fehler, Support und Release-Pflege für die Kunden der studer + raimann ag mit einem entsprechenden Hosting/Wartungsvertrag leistet. Wir veröffentlichen unsere Plugins, weil wir diese sehr gerne auch anderen Community-Mitglieder verfügbar machen möchten. Falls Sie nicht zu unseren Hosting-Kunden gehören, bitten wir Sie um Verständnis, dass wir leider weder kostenlosen Support noch die Release-Pflege für Sie garantieren können.
Sind Sie interessiert an einer Plugin-Patenschaft (https://studer-raimann.ch/produkte/ilias-plugins/plugin-patenschaften/ ) Rufen Sie uns an oder senden Sie uns eine E-Mail.


###Contact
studer + raimann ag

Farbweg 9

3400 Burgdorf

Switzerland 

info@studer-raimann.ch

www.studer-raimann.ch