Powerfolder-Plugin für Stud.IP
===========================

Mit diesem Plugin kann man ab der Stud.IP-Version 4.0 ein Powerfolder als persönlichen Dateibereich einbinden und von und zu den Powerfolder Dateien nach oder von Stud.IP kopieren.

## Installation

1. Das Plugin wird ganz normal in Stud.IP per Drag&Drop installiert. 
2. Powerfolder muss mindestens in Version 11.5 Service Pack 5 installiert sein, damit der OAuth Prozess funktioniert. 
3. Es muss in Powerfolder ein Client angelegt werden. Siehe https://wiki.powerfolder.com/display/PFS/OAuth2+Implementation#OAuth2Implementation-Provideclient-IDandsecret
4. Jetzt hat man einen OAuth2-Client erstellt und kopiert Client-ID und das Secret. Wichtig ist dabei, dass man die korrekte Redirect-URI angibt. Powerfolder überprüft diese URI penibel. Sie sollte in etwa lauten `https://meinstud.ip/plugins.php/powerfolderplugin/oauth/receive_access_token`. Auch sollte HTTPS aktiv sein.

## Credentials übertragen 

1. Melde Dich im Stud.IP als Root an und gehe unter Admin -> System -> Konfiguration -> Powerfolder.
2. Trage die oben gewonnene Client-ID beim Parameter `POWERFOLDER_CLIENT_ID` ein.
3. Trage das oben gewonnene Secret beim Parameter `POWERFOLDER_CLIENT_SECRET` ein.

## Powerfolder im Dateibereich einbinden 

1. Im persönlichen Dateibereich von Stud.IP 4.0 gibt es in der Sidebar den Punkt "Powerfolder konfigurieren". Da muss man drauf klicken.
2. Man muss das Häkchen für "aktiviert" setzen und speichern.
3. Das Fenster lädt sich neu und ein Button erscheint oben "Powerfolder für Stud.IP freigeben". Dort klicken.
4. Dann landet man in Powerfolder und wird aufgefordert sich anzumelden. Vergewissern Sie sich in solchen Situationen immer (nicht nur jetzt), dass die URL stimmt und Sie Ihre Passwörter nicht einer anderen Seite als genau Ihrem Powerfolder zusenden.
5. Powerfolder fragt Sie, ob Stud.IP in Ihrem Namen Daten abrufen und verändern darf. Klicken Sie auf "Authorisieren".
6. Jetzt landen Sie wieder in Stud.IP und die Schnittstelle zwischen Stud.IP und Powerfolder sollte eingerichtet sein.

Diesen letzten Prozess 'Powerfolder in Dateibereich einbinden' muss jeder Nutzende in Stud.IP selbst durchführen. Einen Automatismus dafür gibt es nicht und kann es auch nicht geben, was an der Struktur von OAuth liegt.