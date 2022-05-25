USE taller;

DELIMITER $$
--
-- Funciones
--
DROP PROCEDURE IF EXISTS buscarAdministrador$$
CREATE PROCEDURE buscarAdministrador (_id int)
begin
    select * from administrador where id = _id;
end$$

DROP PROCEDURE IF EXISTS filtrarAdministrador$$
CREATE PROCEDURE filtrarAdministrador (
    _parametros varchar(250), -- %idAdmin%&%nombre%&%apellido1%&%apellido2%&
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
begin
    SELECT cadenaFiltro(_parametros, 'idAdmin&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT * from administrador where ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$

DROP PROCEDURE IF EXISTS numRegsAdministrador$$
CREATE PROCEDURE numRegsAdministrador (
    _parametros varchar(250))
begin
    SELECT cadenaFiltro(_parametros, 'idAdmin&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT count(id) from Administrador where ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$

DROP FUNCTION IF EXISTS nuevoAdministrador$$
CREATE FUNCTION nuevoAdministrador (
    _idAdmin Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _correo Varchar (100))
    RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from Administrador where idAdmin = _idAdmin;
    if _cant < 1 then
        insert into Administrador(idAdmin, nombre, apellido1, apellido2, telefono, 
            correo) 
            values (_idAdmin, _nombre, _apellido1, _apellido2, _telefono, 
            _correo);
    end if;
    return _cant;
end$$

DROP FUNCTION IF EXISTS editarAdministrador$$
CREATE FUNCTION editarAdministrador (
    _id int, 
    _idAdmin Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _correo Varchar (100)
    ) RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from Administrador where id = _id;
    if _cant > 0 then
        update Administrador set
            idAdmin = _idAdmin,
            nombre = _nombre,
            apellido1 = _apellido1,
            apellido2 = _apellido2,
            telefono = _telefono,
            correo = _correo
        where id = _id;
    end if;
    return _cant;
end$$


DROP FUNCTION IF EXISTS eliminarAdministrador$$
CREATE FUNCTION eliminarAdministrador (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    declare _resp int;
    set _resp = 0;
    select count(id) into _cant from administrador where id = _id;
    if _cant > 0 then
        set _resp = 1;
        select count(caso.idCreador) into _cant from administrador
            inner join caso on administrador.idAdmin = caso.idCreador
            where administrador.id = _id;
        if _cant = 0 then
            delete from Administrador where id = _id;
        else 
            -- select 2 into _resp;
            set _resp = 2;
        end if;
    end if;
    return _resp;
end$$

DELIMITER ;
