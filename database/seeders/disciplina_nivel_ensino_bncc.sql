-- Script para relacionar disciplinas com níveis de ensino conforme BNCC
-- Assumindo que as disciplinas já foram inseridas

-- ========================================
-- EDUCAÇÃO INFANTIL (IDs: 1,2,3,4,18,19,38)
-- ========================================
-- Campos de Experiência aplicam-se a todos os níveis da Educação Infantil

-- Berçário I (ID: 18)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 18, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Berçário II (ID: 19)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 19, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Berçário (ID: 1)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 1, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Maternal I (ID: 2)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 2, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Maternal II (ID: 3)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 3, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Pré-Escola (ID: 4)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 4, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- Educação Infantil Geral (ID: 38)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 38, 10, 400, true, d.ordem
FROM disciplinas d 
WHERE d.area_conhecimento = 'Campos de Experiência' AND d.escola_id = 5;

-- ========================================
-- ENSINO FUNDAMENTAL I - ANOS INICIAIS (IDs: 5,6,7,8,9,20,21,22,23,24,39)
-- ========================================

-- 1º Ano Fundamental (IDs: 5, 20)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 5, 
    CASE 
        WHEN d.codigo LIKE 'EF1_LP' THEN 5  -- Língua Portuguesa
        WHEN d.codigo LIKE 'EF1_MAT' THEN 5 -- Matemática
        WHEN d.codigo LIKE 'EF1_CIE' THEN 2 -- Ciências
        WHEN d.codigo LIKE 'EF1_HIS' THEN 2 -- História
        WHEN d.codigo LIKE 'EF1_GEO' THEN 2 -- Geografia
        WHEN d.codigo LIKE 'EF1_ART' THEN 2 -- Arte
        WHEN d.codigo LIKE 'EF1_EF' THEN 2  -- Educação Física
        WHEN d.codigo LIKE 'EF1_ER' THEN 1  -- Ensino Religioso
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EF1_LP' THEN 200
        WHEN d.codigo LIKE 'EF1_MAT' THEN 200
        WHEN d.codigo LIKE 'EF1_CIE' THEN 80
        WHEN d.codigo LIKE 'EF1_HIS' THEN 80
        WHEN d.codigo LIKE 'EF1_GEO' THEN 80
        WHEN d.codigo LIKE 'EF1_ART' THEN 80
        WHEN d.codigo LIKE 'EF1_EF' THEN 80
        WHEN d.codigo LIKE 'EF1_ER' THEN 40
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
WHERE d.codigo LIKE 'EF1_%' AND d.escola_id = 5;

-- Repetir para outros anos do EF1 (IDs: 6,7,8,9,20,21,22,23,24,39)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, nivel.id, 
    CASE 
        WHEN d.codigo LIKE 'EF1_LP' THEN 5
        WHEN d.codigo LIKE 'EF1_MAT' THEN 5
        WHEN d.codigo LIKE 'EF1_CIE' THEN 2
        WHEN d.codigo LIKE 'EF1_HIS' THEN 2
        WHEN d.codigo LIKE 'EF1_GEO' THEN 2
        WHEN d.codigo LIKE 'EF1_ART' THEN 2
        WHEN d.codigo LIKE 'EF1_EF' THEN 2
        WHEN d.codigo LIKE 'EF1_ER' THEN 1
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EF1_LP' THEN 200
        WHEN d.codigo LIKE 'EF1_MAT' THEN 200
        WHEN d.codigo LIKE 'EF1_CIE' THEN 80
        WHEN d.codigo LIKE 'EF1_HIS' THEN 80
        WHEN d.codigo LIKE 'EF1_GEO' THEN 80
        WHEN d.codigo LIKE 'EF1_ART' THEN 80
        WHEN d.codigo LIKE 'EF1_EF' THEN 80
        WHEN d.codigo LIKE 'EF1_ER' THEN 40
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
CROSS JOIN (SELECT id FROM niveis_ensino WHERE id IN (6,7,8,9,20,21,22,23,24,39)) nivel
WHERE d.codigo LIKE 'EF1_%' AND d.escola_id = 5;

-- ========================================
-- ENSINO FUNDAMENTAL II - ANOS FINAIS (IDs: 10,11,12,13,25,26,27,28,40)
-- ========================================

INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, nivel.id, 
    CASE 
        WHEN d.codigo LIKE 'EF2_LP' THEN 4   -- Língua Portuguesa
        WHEN d.codigo LIKE 'EF2_MAT' THEN 4  -- Matemática
        WHEN d.codigo LIKE 'EF2_ING' THEN 2  -- Inglês
        WHEN d.codigo LIKE 'EF2_CIE' THEN 3  -- Ciências
        WHEN d.codigo LIKE 'EF2_HIS' THEN 2  -- História
        WHEN d.codigo LIKE 'EF2_GEO' THEN 2  -- Geografia
        WHEN d.codigo LIKE 'EF2_ART' THEN 2  -- Arte
        WHEN d.codigo LIKE 'EF2_EF' THEN 2   -- Educação Física
        WHEN d.codigo LIKE 'EF2_ER' THEN 1   -- Ensino Religioso
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EF2_LP' THEN 160
        WHEN d.codigo LIKE 'EF2_MAT' THEN 160
        WHEN d.codigo LIKE 'EF2_ING' THEN 80
        WHEN d.codigo LIKE 'EF2_CIE' THEN 120
        WHEN d.codigo LIKE 'EF2_HIS' THEN 80
        WHEN d.codigo LIKE 'EF2_GEO' THEN 80
        WHEN d.codigo LIKE 'EF2_ART' THEN 80
        WHEN d.codigo LIKE 'EF2_EF' THEN 80
        WHEN d.codigo LIKE 'EF2_ER' THEN 40
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
CROSS JOIN (SELECT id FROM niveis_ensino WHERE id IN (10,11,12,13,25,26,27,28,40)) nivel
WHERE d.codigo LIKE 'EF2_%' AND d.escola_id = 5;

-- ========================================
-- ENSINO MÉDIO (IDs: 14,15,16,29,30,31,41)
-- ========================================

-- Formação Comum (obrigatória)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, nivel.id, 
    CASE 
        WHEN d.codigo LIKE 'EM_LP' THEN 3    -- Língua Portuguesa
        WHEN d.codigo LIKE 'EM_MAT' THEN 3   -- Matemática
        WHEN d.codigo LIKE 'EM_ING' THEN 2   -- Inglês
        WHEN d.codigo LIKE 'EM_BIO' THEN 2   -- Biologia
        WHEN d.codigo LIKE 'EM_FIS' THEN 2   -- Física
        WHEN d.codigo LIKE 'EM_QUI' THEN 2   -- Química
        WHEN d.codigo LIKE 'EM_HIS' THEN 2   -- História
        WHEN d.codigo LIKE 'EM_GEO' THEN 2   -- Geografia
        WHEN d.codigo LIKE 'EM_SOC' THEN 2   -- Sociologia
        WHEN d.codigo LIKE 'EM_FIL' THEN 2   -- Filosofia
        WHEN d.codigo LIKE 'EM_ART' THEN 1   -- Arte
        WHEN d.codigo LIKE 'EM_EF' THEN 2    -- Educação Física
        WHEN d.codigo LIKE 'EM_ER' THEN 1    -- Ensino Religioso
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EM_LP' THEN 120
        WHEN d.codigo LIKE 'EM_MAT' THEN 120
        WHEN d.codigo LIKE 'EM_ING' THEN 80
        WHEN d.codigo LIKE 'EM_BIO' THEN 80
        WHEN d.codigo LIKE 'EM_FIS' THEN 80
        WHEN d.codigo LIKE 'EM_QUI' THEN 80
        WHEN d.codigo LIKE 'EM_HIS' THEN 80
        WHEN d.codigo LIKE 'EM_GEO' THEN 80
        WHEN d.codigo LIKE 'EM_SOC' THEN 80
        WHEN d.codigo LIKE 'EM_FIL' THEN 80
        WHEN d.codigo LIKE 'EM_ART' THEN 40
        WHEN d.codigo LIKE 'EM_EF' THEN 80
        WHEN d.codigo LIKE 'EM_ER' THEN 40
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
CROSS JOIN (SELECT id FROM niveis_ensino WHERE id IN (14,15,16,29,30,31,41)) nivel
WHERE d.codigo LIKE 'EM_%' AND d.escola_id = 5;

-- Itinerários Formativos (eletivos)
INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, nivel.id, 2, 80, false, d.ordem
FROM disciplinas d 
CROSS JOIN (SELECT id FROM niveis_ensino WHERE id IN (14,15,16,29,30,31,41)) nivel
WHERE (d.codigo LIKE 'IF_%' OR d.codigo LIKE 'FTP_%') AND d.escola_id = 5;

-- ========================================
-- EJA FUNDAMENTAL (IDs: 32,33,42)
-- ========================================

INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, nivel.id, 
    CASE 
        WHEN d.codigo LIKE 'EJA_F_LP' THEN 4
        WHEN d.codigo LIKE 'EJA_F_MAT' THEN 4
        WHEN d.codigo LIKE 'EJA_F_CIE' THEN 3
        WHEN d.codigo LIKE 'EJA_F_HIS' THEN 2
        WHEN d.codigo LIKE 'EJA_F_GEO' THEN 2
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EJA_F_LP' THEN 160
        WHEN d.codigo LIKE 'EJA_F_MAT' THEN 160
        WHEN d.codigo LIKE 'EJA_F_CIE' THEN 120
        WHEN d.codigo LIKE 'EJA_F_HIS' THEN 80
        WHEN d.codigo LIKE 'EJA_F_GEO' THEN 80
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
CROSS JOIN (SELECT id FROM niveis_ensino WHERE id IN (32,33,42)) nivel
WHERE d.codigo LIKE 'EJA_F_%' AND d.escola_id = 5;

-- ========================================
-- EJA ENSINO MÉDIO (ID: 34)
-- ========================================

INSERT INTO disciplina_nivel_ensino (disciplina_id, nivel_ensino_id, carga_horaria_semanal, carga_horaria_anual, obrigatoria, ordem)
SELECT d.id, 34, 
    CASE 
        WHEN d.codigo LIKE 'EJA_M_LP' THEN 3
        WHEN d.codigo LIKE 'EJA_M_MAT' THEN 3
        WHEN d.codigo LIKE 'EJA_M_BIO' THEN 2
        WHEN d.codigo LIKE 'EJA_M_FIS' THEN 2
        WHEN d.codigo LIKE 'EJA_M_QUI' THEN 2
        WHEN d.codigo LIKE 'EJA_M_HIS' THEN 2
        WHEN d.codigo LIKE 'EJA_M_GEO' THEN 2
        WHEN d.codigo LIKE 'EJA_M_SOC' THEN 1
        WHEN d.codigo LIKE 'EJA_M_FIL' THEN 1
        ELSE 2
    END,
    CASE 
        WHEN d.codigo LIKE 'EJA_M_LP' THEN 120
        WHEN d.codigo LIKE 'EJA_M_MAT' THEN 120
        WHEN d.codigo LIKE 'EJA_M_BIO' THEN 80
        WHEN d.codigo LIKE 'EJA_M_FIS' THEN 80
        WHEN d.codigo LIKE 'EJA_M_QUI' THEN 80
        WHEN d.codigo LIKE 'EJA_M_HIS' THEN 80
        WHEN d.codigo LIKE 'EJA_M_GEO' THEN 80
        WHEN d.codigo LIKE 'EJA_M_SOC' THEN 40
        WHEN d.codigo LIKE 'EJA_M_FIL' THEN 40
        ELSE 80
    END,
    d.obrigatoria, d.ordem
FROM disciplinas d 
WHERE d.codigo LIKE 'EJA_M_%' AND d.escola_id = 5;