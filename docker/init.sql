-- Script de inicialización
-- Se ejecuta automáticamente cuando el contenedor de MySQL
-- arranca por primera vez (solo si db_data está vacío)

USE uden_db_clase2;

-- ── Tabla de autores ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS autores (
    id_autor INT          NOT NULL AUTO_INCREMENT,
    autor    VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_autor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabla de libros ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS libros (
    id_libro INT          NOT NULL AUTO_INCREMENT,
    libro    VARCHAR(255) NOT NULL,
    id_autor INT          NOT NULL,
    PRIMARY KEY (id_libro),
    CONSTRAINT fk_autor
        FOREIGN KEY (id_autor)
        REFERENCES autores (id_autor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Datos de ejemplo (opcional, borra si no los quieres) ──
INSERT INTO autores (autor) VALUES
    ('Gabriel García Márquez'),
    ('Jorge Luis Borges'),
    ('Isabel Allende');

INSERT INTO libros (libro, id_autor) VALUES
    ('Cien años de soledad', 1),
    ('El amor en los tiempos del cólera', 1),
    ('Ficciones', 2),
    ('El Aleph', 2),
    ('La casa de los espíritus', 3);
