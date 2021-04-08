.. _compatibility:

=============
Compatibility
=============

.. rubric:: Table of contents

.. contents::
   :local:

.. _versions:

Version notes
=============

.. _cratedb-versions:

CrateDB
-------

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
+----------------+-----------------+-------------------------------------------+
