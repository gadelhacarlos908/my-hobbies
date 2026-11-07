-- O MyFun passou a ter Mangás/HQs e Músicas. Animes continuam, dentro de Séries.
ALTER TABLE itens_consumo DROP CONSTRAINT itens_consumo_tipo_check;

ALTER TABLE itens_consumo ADD CONSTRAINT itens_consumo_tipo_check
    CHECK (tipo IN ('jogo', 'filme', 'livro', 'manga_hq', 'serie', 'anime', 'musica'));
