-- Создание таблицы users
CREATE TABLE users
(
    id     SERIAL PRIMARY KEY,
    number VARCHAR(255) NOT NULL,
    name   VARCHAR(255) NOT NULL
);

-- Создание таблицы newsletter
CREATE TABLE newsletter
(
    id     SERIAL PRIMARY KEY,
    name   VARCHAR(255) NOT NULL,
    text   TEXT         NOT NULL,
    status VARCHAR(50)  NOT NULL
);

-- Создание таблицы user_newsletter с дополнительной колонкой id
CREATE TABLE user_newsletter
(
    id            SERIAL PRIMARY KEY,
    user_id       INT NOT NULL,
    newsletter_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (newsletter_id) REFERENCES newsletter (id) ON DELETE CASCADE
);