.. _compatibility:

=============
Compatibility
=============

.. _cratedb-versions:

CrateDB Versions
================

Consult the following table for CrateDB version compatibility notes:

+----------------+-----------------+-------------------------------------------+
| Driver Version | CrateDB Version | Notes                                     |
+================+=================+===========================================+
| < 0.4          | Any             | Default schema selection is not           |
|                |                 | supported.                                |
+----------------+-----------------+-------------------------------------------+
| Any            | < 0.55          | Default schema selection is not           |
|                |                 | supported.                                |
+----------------+-----------------+-------------------------------------------+
| Any            | >= 2.1.x        | Client needs to connect with a valid      |
|                |                 | database user to access CrateDB.          |
|                |                 |                                           |
|                |                 | The default CrateDB user is ``crate`` and |
|                |                 | no password is set.                       |
|                |                 |                                           |
|                |                 | The `enterprise edition`_ of CrateDB      |
|                |                 | allows you to `create your own users`_.   |
|                |                 |                                           |
|                |                 | Prior versions of CrateDB do not support  |
|                |                 | this feature.                             |
+----------------+-----------------+-------------------------------------------+

.. _create your own users: https://crate.io/docs/crate/reference/en/latest/admin/user-management.html
.. _enterprise edition: https://crate.io/products/cratedb-enterprise/
