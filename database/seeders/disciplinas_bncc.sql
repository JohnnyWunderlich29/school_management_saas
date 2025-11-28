-- Script para popular disciplinas conforme BNCC
-- Escola ID: 5 (exemplo)

-- ========================================
-- 1. EDUCAÇÃO INFANTIL
-- ========================================
-- Campos de Experiência (não há disciplinas separadas)

INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('O eu, o outro e o nós', 'EI_EOUN', 'Identidade, alteridade, convivência social e ética', 5, 'Campos de Experiência', '#FF6B6B', true, 1),
('Corpo, gestos e movimentos', 'EI_CGM', 'Exploração corporal, motricidade e expressão', 5, 'Campos de Experiência', '#4ECDC4', true, 2),
('Traços, sons, cores e formas', 'EI_TSCF', 'Linguagens artísticas e sensoriais (visuais, sonoras)', 5, 'Campos de Experiência', '#45B7D1', true, 3),
('Escuta, fala, pensamento e imaginação', 'EI_EFPI', 'Desenvolvimento linguístico, narrativo e cognitivo', 5, 'Campos de Experiência', '#96CEB4', true, 4),
('Espaços, tempos, quantidades, relações e transformações', 'EI_ETQRT', 'Noções matemáticas, espaciais e temporais básicas', 5, 'Campos de Experiência', '#FFEAA7', true, 5);

-- ========================================
-- 2. ENSINO FUNDAMENTAL - ANOS INICIAIS (1º ao 5º ano)
-- ========================================

-- LINGUAGENS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Língua Portuguesa', 'EF1_LP', 'Leitura, escrita, oralidade e análise linguística', 5, 'Linguagens', '#E17055', true, 10),
('Arte', 'EF1_ART', 'Artes visuais, música, dança e teatro', 5, 'Linguagens', '#A29BFE', true, 11),
('Educação Física', 'EF1_EF', 'Jogos, brincadeiras e movimentos corporais', 5, 'Linguagens', '#00B894', true, 12);

-- MATEMÁTICA
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Matemática', 'EF1_MAT', 'Números, operações básicas, geometria simples, medidas, estatística inicial', 5, 'Matemática', '#0984E3', true, 20);

-- CIÊNCIAS DA NATUREZA
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Ciências', 'EF1_CIE', 'Matéria/energia, vida/evolução, terra/universo', 5, 'Ciências da Natureza', '#00CEC9', true, 30);

-- CIÊNCIAS HUMANAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('História', 'EF1_HIS', 'Mundo pessoal/comunitário, tempo histórico básico', 5, 'Ciências Humanas', '#FDCB6E', true, 40),
('Geografia', 'EF1_GEO', 'Espaço vivido, orientação espacial básica', 5, 'Ciências Humanas', '#E84393', true, 41);

-- ENSINO RELIGIOSO
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Ensino Religioso', 'EF1_ER', 'Identidades, crenças e pluralismo religioso', 5, 'Ensino Religioso', '#6C5CE7', false, 50);

-- ========================================
-- 3. ENSINO FUNDAMENTAL - ANOS FINAIS (6º ao 9º ano)
-- ========================================

-- LINGUAGENS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Língua Portuguesa', 'EF2_LP', 'Análise textual avançada, gramática, literatura', 5, 'Linguagens', '#E17055', true, 60),
('Língua Inglesa', 'EF2_ING', 'Inglês oral e escrito, comunicação intercultural', 5, 'Linguagens', '#74B9FF', true, 61),
('Arte', 'EF2_ART', 'Artes integradas, expressão artística', 5, 'Linguagens', '#A29BFE', true, 62),
('Educação Física', 'EF2_EF', 'Práticas corporais e esportes', 5, 'Linguagens', '#00B894', true, 63);

-- MATEMÁTICA
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Matemática', 'EF2_MAT', 'Números avançados, álgebra, geometria, medidas, probabilidade/estatística', 5, 'Matemática', '#0984E3', true, 70);

-- CIÊNCIAS DA NATUREZA
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Ciências', 'EF2_CIE', 'Misturas químicas, sistemas biológicos, terra e universo', 5, 'Ciências da Natureza', '#00CEC9', true, 80);

-- CIÊNCIAS HUMANAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('História', 'EF2_HIS', 'Processos históricos e sociais', 5, 'Ciências Humanas', '#FDCB6E', true, 90),
('Geografia', 'EF2_GEO', 'Territórios, globalização, cidadania', 5, 'Ciências Humanas', '#E84393', true, 91);

-- ENSINO RELIGIOSO
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Ensino Religioso', 'EF2_ER', 'Manifestações religiosas, ética e diversidade', 5, 'Ensino Religioso', '#6C5CE7', false, 100);

-- ========================================
-- 4. ENSINO MÉDIO (1º ao 3º ano)
-- ========================================

-- LINGUAGENS E SUAS TECNOLOGIAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Língua Portuguesa', 'EM_LP', 'Literatura, gramática avançada, produção textual', 5, 'Linguagens e suas Tecnologias', '#E17055', true, 110),
('Língua Inglesa', 'EM_ING', 'Inglês avançado, comunicação global', 5, 'Linguagens e suas Tecnologias', '#74B9FF', true, 111),
('Arte', 'EM_ART', 'Artes integradas, cultura e tecnologias', 5, 'Linguagens e suas Tecnologias', '#A29BFE', true, 112),
('Educação Física', 'EM_EF', 'Práticas corporais, saúde e qualidade de vida', 5, 'Linguagens e suas Tecnologias', '#00B894', true, 113);

-- MATEMÁTICA E SUAS TECNOLOGIAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Matemática', 'EM_MAT', 'Raciocínio lógico, aplicações tecnológicas', 5, 'Matemática e suas Tecnologias', '#0984E3', true, 120);

-- CIÊNCIAS DA NATUREZA E SUAS TECNOLOGIAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Biologia', 'EM_BIO', 'Sistemas biológicos, biotecnologia, sustentabilidade', 5, 'Ciências da Natureza e suas Tecnologias', '#00CEC9', true, 130),
('Física', 'EM_FIS', 'Fenômenos físicos, tecnologia e inovação', 5, 'Ciências da Natureza e suas Tecnologias', '#81ECEC', true, 131),
('Química', 'EM_QUI', 'Transformações químicas, materiais e processos', 5, 'Ciências da Natureza e suas Tecnologias', '#55A3FF', true, 132);

-- CIÊNCIAS HUMANAS E SOCIAIS APLICADAS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('História', 'EM_HIS', 'Processos históricos, memória e patrimônio', 5, 'Ciências Humanas e Sociais Aplicadas', '#FDCB6E', true, 140),
('Geografia', 'EM_GEO', 'Territórios, redes e globalização', 5, 'Ciências Humanas e Sociais Aplicadas', '#E84393', true, 141),
('Sociologia', 'EM_SOC', 'Sociedade, cultura e cidadania', 5, 'Ciências Humanas e Sociais Aplicadas', '#FD79A8', true, 142),
('Filosofia', 'EM_FIL', 'Pensamento crítico, ética e estética', 5, 'Ciências Humanas e Sociais Aplicadas', '#FDCB6E', true, 143);

-- ENSINO RELIGIOSO (pode ser integrado)
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Ensino Religioso', 'EM_ER', 'Diversidade religiosa e diálogo intercultural', 5, 'Ensino Religioso', '#6C5CE7', false, 150);

-- ========================================
-- 5. ITINERÁRIOS FORMATIVOS (ENSINO MÉDIO)
-- ========================================

-- LINGUAGENS - ITINERÁRIOS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Mídias Digitais', 'IF_MD', 'Comunicação digital e tecnologias', 5, 'Itinerários - Linguagens', '#E17055', false, 200),
('Literatura e Criação', 'IF_LC', 'Produção literária e criativa', 5, 'Itinerários - Linguagens', '#A29BFE', false, 201);

-- MATEMÁTICA - ITINERÁRIOS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Matemática Aplicada', 'IF_MA', 'Aplicações práticas da matemática', 5, 'Itinerários - Matemática', '#0984E3', false, 210),
('Programação e Algoritmos', 'IF_PA', 'Lógica computacional e programação', 5, 'Itinerários - Matemática', '#74B9FF', false, 211);

-- CIÊNCIAS DA NATUREZA - ITINERÁRIOS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Investigação Científica', 'IF_IC', 'Metodologia científica e pesquisa', 5, 'Itinerários - Ciências da Natureza', '#00CEC9', false, 220),
('Sustentabilidade e Meio Ambiente', 'IF_SMA', 'Ecologia e desenvolvimento sustentável', 5, 'Itinerários - Ciências da Natureza', '#00B894', false, 221);

-- CIÊNCIAS HUMANAS - ITINERÁRIOS
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Cidadania e Direitos Humanos', 'IF_CDH', 'Formação cidadã e direitos', 5, 'Itinerários - Ciências Humanas', '#FDCB6E', false, 230),
('Empreendedorismo Social', 'IF_ES', 'Inovação social e empreendedorismo', 5, 'Itinerários - Ciências Humanas', '#E84393', false, 231);

-- FORMAÇÃO TÉCNICA E PROFISSIONAL
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Gestão e Negócios', 'FTP_GN', 'Administração e gestão empresarial', 5, 'Formação Técnica e Profissional', '#636E72', false, 300),
('Informática', 'FTP_INF', 'Tecnologia da informação', 5, 'Formação Técnica e Profissional', '#74B9FF', false, 301),
('Saúde', 'FTP_SAU', 'Técnico em saúde', 5, 'Formação Técnica e Profissional', '#00CEC9', false, 302),
('Turismo', 'FTP_TUR', 'Técnico em turismo', 5, 'Formação Técnica e Profissional', '#FDCB6E', false, 303);

-- ========================================
-- 6. EJA (EDUCAÇÃO DE JOVENS E ADULTOS)
-- ========================================

-- EJA FUNDAMENTAL
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Língua Portuguesa', 'EJA_F_LP', 'Comunicação e expressão', 5, 'Linguagens', '#E17055', true, 400),
('Matemática', 'EJA_F_MAT', 'Matemática básica e aplicada', 5, 'Matemática', '#0984E3', true, 401),
('Ciências', 'EJA_F_CIE', 'Ciências naturais integradas', 5, 'Ciências da Natureza', '#00CEC9', true, 402),
('História', 'EJA_F_HIS', 'História e cidadania', 5, 'Ciências Humanas', '#FDCB6E', true, 403),
('Geografia', 'EJA_F_GEO', 'Geografia e sociedade', 5, 'Ciências Humanas', '#E84393', true, 404);

-- EJA ENSINO MÉDIO
INSERT INTO disciplinas (nome, codigo, descricao, escola_id, area_conhecimento, cor_hex, obrigatoria, ordem) VALUES
('Língua Portuguesa', 'EJA_M_LP', 'Comunicação avançada e literatura', 5, 'Linguagens e suas Tecnologias', '#E17055', true, 500),
('Matemática', 'EJA_M_MAT', 'Matemática e suas aplicações', 5, 'Matemática e suas Tecnologias', '#0984E3', true, 501),
('Biologia', 'EJA_M_BIO', 'Ciências biológicas', 5, 'Ciências da Natureza e suas Tecnologias', '#00CEC9', true, 502),
('Física', 'EJA_M_FIS', 'Física aplicada', 5, 'Ciências da Natureza e suas Tecnologias', '#81ECEC', true, 503),
('Química', 'EJA_M_QUI', 'Química e transformações', 5, 'Ciências da Natureza e suas Tecnologias', '#55A3FF', true, 504),
('História', 'EJA_M_HIS', 'História contemporânea', 5, 'Ciências Humanas e Sociais Aplicadas', '#FDCB6E', true, 505),
('Geografia', 'EJA_M_GEO', 'Geografia global', 5, 'Ciências Humanas e Sociais Aplicadas', '#E84393', true, 506),
('Sociologia', 'EJA_M_SOC', 'Sociedade e trabalho', 5, 'Ciências Humanas e Sociais Aplicadas', '#FD79A8', true, 507),
('Filosofia', 'EJA_M_FIL', 'Filosofia e ética', 5, 'Ciências Humanas e Sociais Aplicadas', '#FDCB6E', true, 508);