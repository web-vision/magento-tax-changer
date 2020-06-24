# Magento Tax Changer

Aufgrund der sich weltweit ausbreitenden Corona-Pandemie hat die Deutsche Bundesregierung beschlossen, die Mehrwertsteuer (MwSt.) in Deutschland ab dem 1. Juli 2020 für einen Zeitraum von 6 Monaten zu senken.

Nach unserem Kenntnisstand würden die Steueränderungen so aussehen:

* 19% Volle MwSt. -> 16%
* 7% Ermäßigte MwSt. -> 5%

Am Ende dieses Jahres werden die Steuern wieder auf die ursprünglichen Prozentsätze zurückgeführt.

## Was das Skript macht

Der Magento Tax Changer macht Folgendes: 
 
* Fügt neue Steuerklassen, Regeln und Steuersätze zum  Magento 1 oder 2 System hinzu
* Die alten Steuersätze werden nicht angetastet
* Alle Produkte werden geändert, so dass die neuen Steuersätze entsprechend der bisherigen Einstellung angewendet werden.

## Was das Skript nicht macht

Das Skript löscht keine Caches und baut keine Indizes neu auf. Bitte stellen Sie sicher, dass Sie sich selbst um diese Aktionen kümmern. Wenn Sie den kompletten Prozess auf der Kommandozeile oder als CronJob laufen lassen wollen, können Sie die Verbindungs-Option `&&` verwenden, um weitere Befehle für diesen Zweck anzuhängen.

### Beispiel:

```Bash
php tax.php && php bin/magento indexer:reindex && php bin/magento c:c
```

Möglicherweise müssen Sie auch zusätzliche Caches (z. B. Redis, Varnish, CloudFlare) im Voraus überprüfen.

## Installation

Laden Sie die Zip-Datei herunter und entpacken Sie sie im Magento-Stamm.

## Verwendung

Standardmäßig werden die Raten wie im Skript angegeben geändert.

```php
$taxes = [
    19 => 16,
    7 => 5,
];

```

### Neue Steuern erstellen

Gehen Sie in das Tax-Changer-Verzeichnis und führen Sie es auf der Kommandozeile wie folgt aus:

```Bash
php tax.php
```

Wenn Sie Probleme mit Timeouts oder zu wenig Arbeitsspeicher bei der Ausführung des Skripts haben, sollten Sie die PHP-Variablen hierfür bei der Ausführung anpassen.

### Beispiel

```Bash
php -d memory_limit=1024M -d max_execution_time=0 tax.php
```

Bitte passen Sie diese Einstellungen Ihren Bedürfnissen entsprechend an.

Basierend auf den im Skript angegebenen Steuern werden dadurch neue Steuern erstellt und automatisch den Produkten zugeordnet.

### Deaktivieren Sie die neuen Steuern
Mit dem folgenden Befehl werden die neuen Steuersätze deaktiviert und die alten Steuersätze auf die Produkte angewendet. Die neuen Steuersätze werden im System beibehalten, z.B. für Bestellungen während des Jahresabschlusses. 

```Bash
php tax.php -b
```

### Entfernen neuer Steuern

Um die neuen Steuern vollständig aus dem Magento-System zu entfernen, führen Sie

```Bash
php tax.php -r
```

Dadurch werden alle von den Steuerwechslern geschaffenen Steuern entfernt und die alten Steuern jedem Produkt zugeordnet.\

**WICHTIG**: Alle Datensätze werden in der Datenbank-Tabelle *webvision_tax_changer* protokolliert. Ändern oder löschen Sie keine dieser Datensätzen.

## Anmerkungen

Das Skript erstellt keine Steuersatztitel für die einzelnen Geschäfte.

## Garantie
Dieses Skript kommt ohne jegliche Garantie. Bitte benutzen Sie es auf eigenes Risiko und stellen Sie sicher, dass Sie Sicherheitskopien machen und das Skript in einer Staging-/Entwicklungsumgebung testen, bevor Sie es auf einem Produktionssystem ausführen.

## Spende / Lizenz
Dieses Skript ist "Donate Ware" unter der GPL3.0. Wir würden uns freuen, wenn das Skript für Sie nützlich ist, wenn Sie etwas per Paypal spenden könnten. Wenn Sie eine Rechnung benötigen, hinterlassen Sie bitte einen Kommentar in Ihrer Spende.

## PayPal-Spenden-Button

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDGBRLCFRTVPA)
