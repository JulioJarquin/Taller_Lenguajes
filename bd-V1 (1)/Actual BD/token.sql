USE taller;
alter table usuario 
    add tkR varchar(255) null;

DROP FUNCTION IF EXISTS modificarToken;
DROP PROCEDURE IF EXISTS verificarTokenR;

DELIMITER $$

CREATE FUNCTION modificarToken (_idUsuario VARCHAR(15), _tkR varchar(255)) RETURNS INT(1) 
begin
    declare _cant int;
    select count(idUsuario) into _cant from usuario where idUsuario = _idUsuario;
    if _cant > 0 then
        update usuario set
                tkR = _tkR
                where idUsuario = _idUsuario;
        if _tkR <> "" then
            update usuario set
                ultimoAcceso = now()
                where idUsuario = _idUsuario;
        end if;
    end if;
    return _cant;
end$$

CREATE PROCEDURE verificarTokenR (_idUsuario VARCHAR(15), _tkR varchar(255)) 
begin
    select rol from usuario where idUsuario = _idUsuario and tkR = _tkR;
end$$

DELIMITER ;