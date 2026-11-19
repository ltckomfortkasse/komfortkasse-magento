# Komfortkasse-Extension für Magento 1 (veraltet)

Dieses Repository enthält die Komfortkasse-Extension für Magento 1 und ist veraltet, denn Magento 1 erhält seit Juni 2020 keinen Support mehr vom Hersteller; für Magento 2 und Adobe Commerce siehe [komfortkasse-magento2](https://github.com/ltckomfortkasse/komfortkasse-magento2). Komfortkasse ordnet eingehende Überweisungen automatisch den offenen Bestellungen Ihres Shops zu und setzt den Bestellstatus, sobald das Geld da ist.

[Komfortkasse für Magento](https://komfortkasse.eu/shopsysteme/magento/) · [Kostenlos registrieren](https://ssl.komfortkasse.eu/public/register.jsf) · [Installationsanleitung](https://komfortkasse.freshdesk.com/support/solutions/articles/14000115137) · [Hilfe & Dokumentation](https://komfortkasse.freshdesk.com/support/solutions/14000075022)

## Was die Extension in diesem Repository tut

Die Extension (Magento-Modul `Ltc_Komfortkasse`) überträgt offene Bestellungen mit Vorkasse, Rechnung oder Nachnahme an Komfortkasse und setzt den Bestellstatus, wenn Komfortkasse einen Zahlungseingang oder eine Stornierung meldet. Vorhandene offene Rechnungen (Invoices) markiert sie dabei als bezahlt; das lässt sich in den Einstellungen abschalten. Gutschriften (Credit Memos) stellt sie Komfortkasse für Rückerstattungen bereit.

Neue Bestellungen und Statuswechsel meldet die Extension sofort an Komfortkasse, ausschließlich per HTTPS an `ssl.komfortkasse.eu` und mit Prüfung des Zertifikats; steht HTTPS nicht zur Verfügung, übernimmt Komfortkasse die Bestellung beim nächsten regelmäßigen Abruf. Komfortkasse ruft die Extension über `<Shop-URL>/komfortkasse/main/<Aktion>` auf; Bestelldaten gibt sie nur gegen den Zugriffscode heraus.

## Installation

Die Extension liegt als Magento-Connect-Paket `Ltc_Komfortkasse.tgz` beim [neuesten Release](https://github.com/ltckomfortkasse/komfortkasse-magento/releases/latest). Sie installieren es im Magento Connect Manager unter „Direct package file upload“ oder entpacken es in das Hauptverzeichnis von Magento (Ordner `app/code/community/Ltc/Komfortkasse` und Datei `app/etc/modules/Ltc_Komfortkasse.xml`). Danach den Cache leeren und bei aktivierter Kompilierung neu kompilieren. Die Einstellungen stehen unter System → Configuration → Payment Methods → Komfortkasse. Die [Installationsanleitung](https://komfortkasse.freshdesk.com/support/solutions/articles/14000115137) beschreibt alle Schritte.

## Andere Shopsysteme

Komfortkasse gibt es unter anderem für Shopify, Shopware, WooCommerce, PrestaShop, Gambio und xt:Commerce. Alle Systeme stehen in der [Übersicht der Shopsysteme](https://komfortkasse.eu/shopsysteme/), weitere Plugin-Repositories unter [github.com/ltckomfortkasse](https://github.com/ltckomfortkasse).

## English

**Komfortkasse extension for Magento 1 (legacy).** Magento 1 reached its end of life in June 2020; for Magento 2 and Adobe Commerce use [komfortkasse-magento2](https://github.com/ltckomfortkasse/komfortkasse-magento2). Komfortkasse automatically matches incoming bank transfers to open orders in your shop and updates the order status as soon as the payment has arrived. Install `Ltc_Komfortkasse.tgz` from the [latest release](https://github.com/ltckomfortkasse/komfortkasse-magento/releases/latest) via "Direct package file upload" in Magento Connect Manager or extract it into the Magento root. More information: [Komfortkasse for Magento](https://komfortkasse.eu/shopsysteme/magento/) · [Installation guide (English)](https://komfortkasse.freshdesk.com/support/solutions/articles/14000152608)
