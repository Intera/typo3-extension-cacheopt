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

Excluding tables from reference index traveral
----------------------------------------------

It does not make sense to traverse the reference index for all tables and all fields.

Default configuration
~~~~~~~~~~~~~~~~~~~~~

Some tables are already excluded by default to prevent endless cache
clearing processes which might occur when a record is related to
many other records, like sys_langauge:

- ``fe_groups``
- ``fe_users``
- ``sys_file_storage``
- ``sys_language``

Exclude addtional tables / fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To exclude an additional table from refindex traversal you can also use
the registry:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerExcludedTable('tx_myext_mytable');

In some cases it can also make sense to exclude certain fields from refindex
traversal to prevent a too exhaustive cache clearing. For this you can use:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerExcludedFieldForTable('tx_myext_mytable', 'my_excluded_field');

Prevent automatic exclusion
~~~~~~~~~~~~~~~~~~~~~~~~~~~

All tables registered to plugins or content types will be excluded by
default because in most cases it is enough to clear the cache for the
related content elements.

To prevent this you can provide an additional third parameter to the register
methods:

::

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerContentForTable('tx_myext_mytable', 'my_content_type', FALSE);

  \Tx\Cacheopt\CacheOptimizerRegistry::getInstance()
  	->registerPluginForTable('tx_myext_mytable', 'my_plugin_type', FALSE);

