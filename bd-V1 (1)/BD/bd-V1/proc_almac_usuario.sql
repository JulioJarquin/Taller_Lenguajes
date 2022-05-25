USE taller;

DELIMITER $$
--
-- Funciones
--
DROP PROCEDURE IF EXISTS todosCliente$$
CREATE PROCEDURE todosCliente (_pagina SMALLINT UNSIGNED, _cantRegs SMALLINT UNSIGNED)
begin
    select * from cliente limit _pagina, _cantRegs;
end$$

DROP PROCEDURE IF EXISTS buscarUsuario$$
CREATE PROCEDURE buscarUsuario(_idUsuario varchar(15))
begin
    select * from usuario where idUsuario = _idUsuario;
end$$

DROP PROCEDURE IF EXISTS filtrarCliente$$
CREATE PROCEDURE filtrarCliente (
    _parametros varchar(250), -- %idCliente%&%nombre%&%apellido1%&%apellido2%&
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
begin
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT * from cliente where ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;   
end$$

DROP PROCEDURE IF EXISTS numRegsCliente$$
CREATE PROCEDURE numRegsCliente (
    _parametros varchar(250))
begin
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT count(id) from cliente where ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$



DROP FUNCTION IF EXISTS nuevoUsuario$$
CREATE FUNCTION nuevoUsuario (
    _idUsuario Varchar(15),
    _rol int,
    _passw Varchar (255))
    RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from usuario where idUsuario = _idUsuario;
    if _cant < 1 then
        insert into usuario(idUsuario, rol, passw) 
            values (_idUsuario, _rol, _passw);
    end if;
    return _cant;
end$$

DROP FUNCTION IF EXISTS editarCliente$$
CREATE FUNCTION editarCliente (
    _id int, 
    _idCliente Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _celular Varchar (9),
    _direccion Varchar (255),
    _correo Varchar (100)
    ) RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from cliente where id = _id;
    if _cant > 0 then
        update cliente set
            idCliente = _idCliente,
            nombre = _nombre,
            apellido1 = _apellido1,
            apellido2 = _apellido2,
            telefono = _telefono,
            celular = _celular,
            direccion = _direccion,
            correo = _correo
        where id = _id;
    end if;
    return _cant;
end$$

DROP FUNCTION IF EXISTS eliminarCliente$$
CREATE FUNCTION eliminarCliente (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    declare _resp int;
    set _resp = 0;
    select count(id) into _cant from cliente where id = _id;
    if _cant > 0 then
        set _resp = 1;
        select count(id) into _cant from artefacto where idCliente = _id;
        if _cant = 0 then
            delete from cliente where id = _id;
        else 
            -- select 2 into _resp;
            set _resp = 2;
        end if;
    end if;
    return _resp;
end$$

DELIMITER ;
