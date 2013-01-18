# Snapper

MySQL snapshot generation tool written for PHP 5.3  
<https://github.com/nickwhitt/Snapper>

Generates one file per table for all databases on a MySQL server. Uses native `PDO` for database and table scanning, and `mysqldump` for table exports.

## Usage

### Export

    $server = new Snapper\Server($username, $password, $host);
    $server->scanDatabases();
    $server->exportDatabases();

### Options

Exclude Databases before scanning:

    $server->excludeDatabases(array('information_schema', 'mysql', 'test'));

Pass a `mysql.cnf` file to the `mysqldump` command:

    $server->exportDatabases('/path/to/mysql.cnf', 'path/to/export');

## Copyright
Copyright (c) 2012, Nicholas Whitt (<nick.whitt@gmail.com>)

## License
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.