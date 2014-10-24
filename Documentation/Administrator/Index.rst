.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

.. _admin-installation:

Installation
------------

Import the extension in the extension manager and install it.

You need to make sure that your reference index is up to date. You can
either use the DB check Backend module or the command line:

::

  php /path/to/typo3/installation/typo3/cli_dispatch.phpsh lowlevel_refindex -e


This Extension works out of the box with no special configuration needed
for default TYPO3 installations.

For Extensions additional configuration is needed. Default configuration
is included for:

- news
- cz_simple_cal

If you have additional Extensions that are not supported yet you can:

- Look at the section :ref:`developers-guide` to see how you can configure additional Extensions.
- Open an `issue on Github`_ and request the Extension to be included in the default configuration.

.. _issue on Github: https://github.com/Intera/typo3-extension-cacheopt/issues/