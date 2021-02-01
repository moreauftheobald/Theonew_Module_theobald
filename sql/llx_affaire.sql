-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_affaire(
	-- BEGIN MODULEBUILDER FIELDS
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
	entity INTEGER DEFAULT 1 NOT NULL,
	qty INTEGER DEFAULT 1 NOT NULL,
	fk_soc INTEGER NOT NULL,
	fk_product INTEGER NOT NULL,
	tms timestamp NOT NULL,
	fk_commande INTEGER,
	fk_user_creat INTEGER NOT NULL,
	fk_user_modif INTEGER NOT NULL,
	date_creation datetime NOT NULL,
	status INTEGER NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;