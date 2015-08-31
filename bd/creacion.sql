DROP DATABASE IF EXISTS nosjuntemos;
CREATE DATABASE nosjuntemos;
USE nosjuntemos;

CREATE TABLE dias (
    id INT NOT NULL AUTO_INCREMENT, 
    nombre VARCHAR(12) NOT NULL,
    PRIMARY KEY(id)
)ENGINE InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE reuniones (
    id INT NOT NULL AUTO_INCREMENT, 
    lugar VARCHAR(100) NOT NULL, 
    hash VARCHAR(256) NOT NULL, 
    hora_inicio TIME NOT NULL, 
    hora_fin TIME NOT NULL, 
    fecha_creacion DATETIME NOT NULL DEFAULT NOW(),
    fecha_reunion DATETIME NOT NULL,
    PRIMARY KEY(id)
)ENGINE InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE dias_x_reunion (
    id INT NOT NULL AUTO_INCREMENT, 
    id_reunion INT NOT NULL,
    id_dia INT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(id_reunion) REFERENCES reuniones(id),
    FOREIGN KEY(id_dia) REFERENCES dias(id)
)ENGINE InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE asistencias (
    id INT NOT NULL AUTO_INCREMENT, 
    id_reunion INT NOT NULL, 
    nombre VARCHAR(40) NOT NULL, 
    fecha DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id),
    FOREIGN KEY(id_reunion) REFERENCES reuniones(id)
)ENGINE InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE horas_x_asistencia (
    id INT NOT NULL AUTO_INCREMENT, 
    id_asistencia INT NOT NULL, 
    id_dia INT NOT NULL, 
    hora_inicio TIME NOT NULL, 
    hora_fin TIME NOT NULL, 
    PRIMARY KEY(id),
    FOREIGN KEY(id_asistencia) REFERENCES asistencias(id),
    FOREIGN KEY(id_dia) REFERENCES dias(id)
)ENGINE InnoDB DEFAULT CHARACTER SET=utf8;

INSERT INTO dias(nombre) VALUES ("Lunes"), ("Martes"), ("Miércoles"), ("Viernes"), ("Sábado"), ("Domingo");