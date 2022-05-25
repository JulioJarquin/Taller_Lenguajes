USE taller;

DELIMITER $$
--
-- Funciones
--
DROP PROCEDURE IF EXISTS todosOficinista$$
CREATE PROCEDURE todosOficinista (_pagina SMALLINT UNSIGNED, _cantRegs SMALLINT UNSIGNED)
begin
    select * from oficinista limit _pagina, _cantRegs;
end$$
/**/









DROP PROCEDURE IF EXISTS buscarOficinista$$
CREATE PROCEDURE buscarOficinista (_id int,_idOficinista varchar(15))
begin
    select * from oficinista where id = _id or idOficinista=_idOficinista;
end$$
/**/











DROP PROCEDURE IF EXISTS filtrarOficinista$$
CREATE PROCEDURE filtrarOficinista (
    _parametros varchar(250),
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
begin
    SELECT cadenaFiltro(_parametros, 'idOficinista&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT * from oficinista where ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$











DROP PROCEDURE IF EXISTS numRegsOficinista$$
CREATE PROCEDURE numRegsOficinista (
    _parametros varchar(250))
begin
    SELECT cadenaFiltro(_parametros, 'idOficinista&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT count(id) from oficinista where ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$











DROP FUNCTION IF EXISTS nuevoOficinista$$
CREATE FUNCTION nuevoOficinista (
    _idOficinista Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _celular Varchar (9),
    _direccion Varchar (255),
    _correo Varchar (100))
    RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from oficinista where idOficinista = _idOficinista;
    if _cant < 1 then
        insert into oficinista(idOficinista, nombre, apellido1, apellido2, telefono, 
            celular, direccion, correo) 
            values (_idOficinista, _nombre, _apellido1, _apellido2, _telefono, 
            _celular, _direccion, _correo);
    end if;
    return _cant;
end$$















DROP FUNCTION IF EXISTS editarOficinista$$
CREATE FUNCTION editarOficinista (
    _id int, 
    _idOficinista Varchar(15),
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
    select count(id) into _cant from oficinista where id = _id;
    if _cant > 0 then
        update oficinista set
            idOficinista = _idOficinista,
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
















DROP FUNCTION IF EXISTS eliminarOficinista$$
CREATE FUNCTION eliminarOficinista (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    declare _resp int;
    set _resp = 0;
    select count(id) into _cant from oficinista where id = _id;
    if _cant > 0 then
        set _resp = 1;
        select count(idCreador) into _cant from caso where idCreador = _id;
        if _cant = 0 then
            set _resp = 1;
            delete from oficinista where id = _id;
        else 
            -- select 2 into _resp;
            set _resp = 0;
        end if;
    end if;
    return _resp;
end$$












DELIMITER ;
