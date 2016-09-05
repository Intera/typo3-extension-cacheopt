.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developers-guide:

Extension Developers
====================

.. _developers-plugins-and-content:

Register plugins and content types
----------------------------------

**Please note that this is only a workaround for Extensions that do not properly handle caching.** It is
not optimal because it clears the cache of all pages where the related plugin is used. Do not use in sites
with high performance requirements!

The cacheopt Extension needs to know which tables belong to which content
type or which plugin type. This information is stored in the
:php:`CacheOptimizerRegistry`.

To connect a table to a content type, you can use this command in the
``ext_localconf.php`` file of your Extension:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerContentForTable('tx_myext_mytable', 'my_content_type');

After adding this configuration the cache for all pages is cleared where
content elements with the CType ``my_content_type`` are present when a
``tx_myext_mytable`` record is changed.

The configuration for plugin types is basically the same:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerPluginForTable('tx_myext_mytable', 'my_plugin_type');

There are also methods for connecting multiple tables with content or
plugin types:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()->registerContentForTables(
    array(
      'tx_myext_mytable1',
      'tx_myext_mytable2'
    ),
    'my_content_type'
  );

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()->registerPluginForTables(
    array(
      'tx_myext_mytable1',
      'tx_myext_mytable2'
    ),
    'my_plugin_type'
  );

