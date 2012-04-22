=== LH RDF ===
Contributors: shawfactor
Donate link: http://localhero.biz/plugins/lh-rdf/
Tags: feed, feeds, rdf, localhero, sioc, skos, foaf
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: trunk

This plugin allows allows the publishing of RDF xml metadata from your weblog in a format compliant with the SIOC specification. Additionally wordpress specific post metadata is also published.

== Description ==

Once activated the plugin adds a new type of feed that can be subscribed to. E.G. http://example.com/?feed=lhrdf or http://example.com/feed/lhrdf/ containing semantic content. It has been developed for use in [LocalHero][].

[LocalHero]: http://localhero.biz/

LH RDF is inspired and to some extent based on the original wordpress SIOC exporter: http://sioc-project.org/wordpress.

== Installation ==

1. Upload the entire `lh-rdf` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. To enable pretty permlinks (e.g. `http://example.com/feed/lhrdf/`), go to Permalinks in the Setting menu and Save.

== Changelog ==

**0.1.3 April 22, 2012**  
* Added dcterms identifier

**0.1.2 March 02, 2012**  
* Fixed RDf bug, added tag feed

**0.1.1 January 05, 2012**  
* Fixed critical bug

**0.1.0 December 26, 2011**  
* Fixed Date Scheme

**0.0.9 October 5, 2011**  
* Fixed feed permalinks

**0.0.8 September 25, 2011**  
* Fixed Bugs

**0.0.7 September 22, 2011**  
* Added Post thumbnail support

**0.0.6 September 19, 2011**  
* Fixed SIOC topics and added SIOC terms

**0.0.5 September 19, 2011**  
Added custom post type support

**0.0.4 September 19, 2011**  
Added content negotiation.

**0.0.3 September 19, 2011**  
Autodiscovery.

**0.0.2 September 15, 2011**  
Bug fix.

**0.0.1 August 15, 2011**  
Initial release.