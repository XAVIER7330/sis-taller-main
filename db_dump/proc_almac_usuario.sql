USE taller;
DELIMITER $$
DROP PROCEDURE IF EXISTS buscarUsuario$$
CREATE PROCEDURE buscarUsuario (_id int(11), _idUsuario varchar(15))
begin
    select * from usuario where idUsuario = _idUsuario or id = _id;
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
DROP FUNCTION IF EXISTS eliminarUsuario$$
CREATE FUNCTION eliminarUsuario (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    select count(id) into _cant from usuario where id = _id;
    if _cant > 0 then
        delete from usuario where id = _id;
    end if;
    return _cant;
end$$
DROP FUNCTION IF EXISTS rolUsuario$$
CREATE FUNCTION rolUsuario (
    _id int, 
    _rol int
    ) RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from usuario where id = _id;
    if _cant > 0 then
        update usuario set
            rol = _rol
        where id = _id;
    end if;
    return _cant;
end$$
DROP FUNCTION IF EXISTS passwUsuario$$
CREATE FUNCTION passwUsuario (
    _id int, 
    _passw Varchar(255)
    ) RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from usuario where id = _id;
    if _cant > 0 then
        update usuario set
            passw = _passw
        where id = _id;
    end if;
    return _cant;
end$$
DELIMITER ;
