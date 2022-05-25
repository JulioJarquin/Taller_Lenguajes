USE taller;

DELIMITER $$
--
-- Funciones
--
DROP PROCEDURE IF EXISTS todosTecnico$$
CREATE PROCEDURE todosTecnico (_pagina SMALLINT UNSIGNED, _cantRegs SMALLINT UNSIGNED)
begin
    select * from tecnico limit _pagina, _cantRegs;
end$$
/**/









DROP PROCEDURE IF EXISTS buscarTecnico$$
CREATE PROCEDURE buscarTecnico (_id int,_idTecnico varchar(15))
begin
    select * from tecnico where id = _id or idTecnico=_idTecnico;
end$$
/**/











DROP PROCEDURE IF EXISTS filtrarTecnico$$
CREATE PROCEDURE filtrarTecnico (
    _parametros varchar(250),
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
begin
    SELECT cadenaFiltro(_parametros, 'idTecnico&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT * from tecnico where ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$











DROP PROCEDURE IF EXISTS numRegsTecnico$$
CREATE PROCEDURE numRegsTecnico (
    _parametros varchar(250))
begin
    SELECT cadenaFiltro(_parametros, 'idTecnico&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT count(id) from tecnico where ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$











DROP FUNCTION IF EXISTS nuevoTecnico$$
CREATE FUNCTION nuevoTecnico (
    _idTecnico Varchar(15),
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
    select count(id) into _cant from tecnico where idTecnico = _idTecnico;
    if _cant < 1 then
        insert into tecnico(idTecnico, nombre, apellido1, apellido2, telefono, 
            celular, direccion, correo) 
            values (_idTecnico, _nombre, _apellido1, _apellido2, _telefono, 
            _celular, _direccion, _correo);
    end if;
    return _cant;
end$$















DROP FUNCTION IF EXISTS editarTecnico$$
CREATE FUNCTION editarTecnico (
    _id int, 
    _idTecnico Varchar(15),
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
    select count(id) into _cant from tecnico where id = _id;
    if _cant > 0 then
        update tecnico set
            idTecnico = _idTecnico,
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
















DROP FUNCTION IF EXISTS eliminarTecnico$$
CREATE FUNCTION eliminarTecnico (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    declare _resp int;
    set _resp = 0;
    select count(id) into _cant from tecnico where id = _id;
    if _cant > 0 then
        set _resp = 1;
        select count(idTecnico) into _cant from caso where idTecnico = _id;
        if _cant = 0 then
            set _resp = 1;
            delete from tecnico where id = _id;
        else 
            -- select 2 into _resp;
            set _resp = 0;
        end if;
    end if;
    return _resp;
end$$












DELIMITER ;
