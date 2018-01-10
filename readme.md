Powerfolder-Plugin f�r Stud.IP
===========================

Mit diesem Plugin kann man ab der Stud.IP-Version 4.0 ein Powerfolder als pers�nlichen Dateibereich einbinden und von und zu den Powerfolder Dateien nach oder von Stud.IP kopieren.

## Installation

1. Das Plugin wird ganz normal in Stud.IP per Drag&Drop installiert. 
2. Powerfolder muss mindestens in Version 11.5 Service Pack 5 installiert sein, damit der OAuth Prozess funktioniert. 
3. Es muss in Owncloud ein Client angelegt werden. Unter Administration -> "Additional" einen neuen Client anlegen. Name ist dabei egal (vielleicht ja Stud.IP).
4. Jetzt hat man einen OAuth2-Client erstellt und kopiert Client-ID und das Secret. Wichtig ist dabei, dass man die korrekte Redirect-URI angibt. Owncloud �berpr�ft diese URI penibel. Sie sollte in etwa lauten `https://meinstud.ip/plugins.php/owncloudplugin/oauth/receive_access_token`. Auch sollte HTTPS aktiv sein.

Jetzt muss man sich �berlegen, wie das Owncloud-Plugin genutzt werden soll in Stud.IP. Gibt es eine zentrale OwnCloud f�r alle Stud.IP-Nutzer oder k�mmern sich die Nutzer selbst um eine eigene OwnCloud?

Zentral: 

1. Melde Dich im Stud.IP als Root an und gehe unter Admin -> System -> Konfiguration -> Owncloud.
2. Trage die oben gewonnene Client-ID beim Parameter `OWNCLOUD_CLIENT_ID` ein.
3. Trage das oben gewonnene Secret beim Parameter `OWNCLOUD_CLIENT_SECRET` ein.

Individuell:

1. Der Nutzer muss dann alleine die obigen Schritte durchf�hren bzw. seinen OwnCloud-Admin fragen, ob er das f�r ihn tun kann und die Credentials �bergibt.
2. Im pers�nlichen Dateibereich von Stud.IP 4.0 gibt es in der Sidebar den Punkt "OwnCloud konfigurieren". Da muss er drauf klicken. Ein Dialog �ffnet sich.
3. Man muss die Adresse der OwnCloud eintragen (z.B. `https://meineuni/owncloud`) und App-ID und Secret von oben eintragen und die OwnCloud aktiv schalten und speichern.

Die n�chsten Schritte sind f�r beide Wege wieder dieselben:

1. Wer es individuell eingestellt hat, kennt es schon: Im pers�nlichen Dateibereich von Stud.IP 4.0 gibt es in der Sidebar den Punkt "OwnCloud konfigurieren". Da muss man drauf klicken.
2. Man muss das H�kchen f�r "aktiviert" setzen und speichern.
3. Das Fenster l�dt sich neu und ein Button erscheint oben "OwnCloud f�r Stud.IP freigeben". Dort klicken.
4. Dann landet man in der OwnCloud und wird aufgefordert sich anzumelden. Vergewissern Sie sich in solchen Situationen immer (nicht nur jetzt), dass die URL stimmt und Sie Ihre Passw�rter nicht einer anderen Seite als genau Ihrer OwnCloud zusenden.
5. Die OwnCloud fragt Sie, ob Stud.IP in Ihrem Namen Daten abrufen und ver�ndern darf. Klicken Sie auf "Authorisieren".
6. Jetzt landen Sie wieder in Stud.IP und die Schnittstelle zwischen Stud.IP und OwnCloud sollte eingerichtet sein.


