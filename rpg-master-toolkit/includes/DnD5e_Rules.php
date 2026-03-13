<?php
/**
 * RPG Master Toolkit - D&D 5e Rules Engine
 * 
 * Regras, raças, classes, backgrounds, cálculos de modificadores,
 * tabela de proficiência, XP por nível, etc.
 */

namespace RMT;

if ( ! defined( 'ABSPATH' ) ) exit;

class DnD5e_Rules {

    // =============================================
    // RAÇAS (SRD 5e)
    // =============================================
    public static function get_races() {
        return array(
            'human' => array(
                'name'    => 'Humano',
                'bonuses' => array( 'str' => 1, 'dex' => 1, 'con' => 1, 'int' => 1, 'wis' => 1, 'cha' => 1 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', '+1 à escolha' ),
                'traits'  => array( 'Versátil: +1 em todos os atributos' ),
                'subraces'=> array(),
            ),
            'elf' => array(
                'name'    => 'Elfo',
                'bonuses' => array( 'dex' => 2 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Élfico' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Ancestralidade Feérica', 'Transe' ),
                'subraces'=> array(
                    'high_elf' => array(
                        'name' => 'Alto Elfo',
                        'bonuses' => array( 'int' => 1 ),
                        'traits' => array( 'Truque de Mago', 'Idioma extra' ),
                    ),
                    'wood_elf' => array(
                        'name' => 'Elfo da Floresta',
                        'bonuses' => array( 'wis' => 1 ),
                        'speed' => 35,
                        'traits' => array( 'Máscara da Natureza', 'Pés Ligeiros' ),
                    ),
                    'dark_elf' => array(
                        'name' => 'Drow',
                        'bonuses' => array( 'cha' => 1 ),
                        'traits' => array( 'Visão no Escuro Superior (120ft)', 'Sensibilidade à Luz Solar', 'Magia Drow' ),
                    ),
                ),
            ),
            'dwarf' => array(
                'name'    => 'Anão',
                'bonuses' => array( 'con' => 2 ),
                'speed'   => 25,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Anão' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Resistência Anã', 'Treinamento em Combate Anão', 'Conhecimento de Pedra' ),
                'subraces'=> array(
                    'hill_dwarf' => array(
                        'name' => 'Anão da Colina',
                        'bonuses' => array( 'wis' => 1 ),
                        'traits' => array( 'Tenacidade Anã (+1 HP por nível)' ),
                    ),
                    'mountain_dwarf' => array(
                        'name' => 'Anão da Montanha',
                        'bonuses' => array( 'str' => 2 ),
                        'traits' => array( 'Proficiência com armaduras leves e médias' ),
                    ),
                ),
            ),
            'halfling' => array(
                'name'    => 'Halfling',
                'bonuses' => array( 'dex' => 2 ),
                'speed'   => 25,
                'size'    => 'Small',
                'languages' => array( 'Comum', 'Halfling' ),
                'traits'  => array( 'Sortudo', 'Bravura', 'Agilidade Halfling' ),
                'subraces'=> array(
                    'lightfoot' => array(
                        'name' => 'Pés Leves',
                        'bonuses' => array( 'cha' => 1 ),
                        'traits' => array( 'Furtividade Natural' ),
                    ),
                    'stout' => array(
                        'name' => 'Robusto',
                        'bonuses' => array( 'con' => 1 ),
                        'traits' => array( 'Resistência a Veneno' ),
                    ),
                ),
            ),
            'dragonborn' => array(
                'name'    => 'Draconato',
                'bonuses' => array( 'str' => 2, 'cha' => 1 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Dracônico' ),
                'traits'  => array( 'Ancestralidade Dracônica', 'Arma de Sopro', 'Resistência a Dano' ),
                'subraces'=> array(),
            ),
            'gnome' => array(
                'name'    => 'Gnomo',
                'bonuses' => array( 'int' => 2 ),
                'speed'   => 25,
                'size'    => 'Small',
                'languages' => array( 'Comum', 'Gnômico' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Esperteza Gnômica' ),
                'subraces'=> array(
                    'rock_gnome' => array(
                        'name' => 'Gnomo das Rochas',
                        'bonuses' => array( 'con' => 1 ),
                        'traits' => array( 'Conhecimento de Artífice', 'Engenhoqueiro' ),
                    ),
                    'forest_gnome' => array(
                        'name' => 'Gnomo da Floresta',
                        'bonuses' => array( 'dex' => 1 ),
                        'traits' => array( 'Ilusionista Natural', 'Falar com Pequenos Animais' ),
                    ),
                ),
            ),
            'half_elf' => array(
                'name'    => 'Meio-Elfo',
                'bonuses' => array( 'cha' => 2 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Élfico', '+1 à escolha' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Ancestralidade Feérica', 'Versatilidade em Perícias (+2 skills)', '+1 em dois atributos à escolha' ),
                'subraces'=> array(),
            ),
            'half_orc' => array(
                'name'    => 'Meio-Orc',
                'bonuses' => array( 'str' => 2, 'con' => 1 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Orc' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Ameaçador', 'Resistência Implacável', 'Ataques Selvagens' ),
                'subraces'=> array(),
            ),
            'tiefling' => array(
                'name'    => 'Tiefling',
                'bonuses' => array( 'cha' => 2, 'int' => 1 ),
                'speed'   => 30,
                'size'    => 'Medium',
                'languages' => array( 'Comum', 'Infernal' ),
                'traits'  => array( 'Visão no Escuro (60ft)', 'Resistência Infernal (fogo)', 'Legado Infernal' ),
                'subraces'=> array(),
            ),
        );
    }

    // =============================================
    // CLASSES (SRD 5e)
    // =============================================
    public static function get_classes() {
        return array(
            'barbarian' => array(
                'name'           => 'Bárbaro',
                'hit_die'        => 12,
                'primary_ability'=> 'str',
                'saving_throws'  => array( 'str', 'con' ),
                'armor_prof'     => array( 'Leve', 'Média', 'Escudos' ),
                'weapon_prof'    => array( 'Simples', 'Marciais' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'animal_handling', 'athletics', 'intimidation', 'nature', 'perception', 'survival' ),
                'starting_hp'    => 12,
                'subclass_level' => 3,
                'subclass_name'  => 'Caminho Primitivo',
                'features' => array(
                    1 => array( 'Fúria', 'Defesa sem Armadura' ),
                    2 => array( 'Ataque Descuidado', 'Sentido de Perigo' ),
                    3 => array( 'Caminho Primitivo' ),
                    5 => array( 'Ataque Extra', 'Movimento Rápido' ),
                ),
            ),
            'bard' => array(
                'name'           => 'Bardo',
                'hit_die'        => 8,
                'primary_ability'=> 'cha',
                'saving_throws'  => array( 'dex', 'cha' ),
                'armor_prof'     => array( 'Leve' ),
                'weapon_prof'    => array( 'Simples', 'Bestas de mão', 'Espadas longas', 'Rapieiras', 'Espadas curtas' ),
                'skill_choices'  => 3,
                'skill_list'     => array( 'acrobatics', 'animal_handling', 'arcana', 'athletics', 'deception', 'history', 'insight', 'intimidation', 'investigation', 'medicine', 'nature', 'perception', 'performance', 'persuasion', 'religion', 'sleight_of_hand', 'stealth', 'survival' ),
                'spellcasting'   => true,
                'spell_ability'  => 'cha',
                'starting_hp'    => 8,
                'subclass_level' => 3,
                'subclass_name'  => 'Colégio de Bardo',
                'features' => array(
                    1 => array( 'Conjuração', 'Inspiração Bárdica (d6)' ),
                    2 => array( 'Versatilidade', 'Canção do Descanso (d6)' ),
                    3 => array( 'Colégio de Bardo', 'Especialização' ),
                    5 => array( 'Inspiração Bárdica (d8)', 'Fonte de Inspiração' ),
                ),
            ),
            'cleric' => array(
                'name'           => 'Clérigo',
                'hit_die'        => 8,
                'primary_ability'=> 'wis',
                'saving_throws'  => array( 'wis', 'cha' ),
                'armor_prof'     => array( 'Leve', 'Média', 'Escudos' ),
                'weapon_prof'    => array( 'Simples' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'history', 'insight', 'medicine', 'persuasion', 'religion' ),
                'spellcasting'   => true,
                'spell_ability'  => 'wis',
                'starting_hp'    => 8,
                'subclass_level' => 1,
                'subclass_name'  => 'Domínio Divino',
                'features' => array(
                    1 => array( 'Conjuração', 'Domínio Divino' ),
                    2 => array( 'Canalizar Divindade (1x)', 'Canalizar: Expulsar Mortos-vivos' ),
                    5 => array( 'Destruir Mortos-vivos (CR 1/2)' ),
                ),
            ),
            'druid' => array(
                'name'           => 'Druida',
                'hit_die'        => 8,
                'primary_ability'=> 'wis',
                'saving_throws'  => array( 'int', 'wis' ),
                'armor_prof'     => array( 'Leve', 'Média', 'Escudos (não-metal)' ),
                'weapon_prof'    => array( 'Clavas', 'Adagas', 'Dardos', 'Javelinas', 'Maças', 'Bordões', 'Cimitarras', 'Fundas', 'Lanças' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'arcana', 'animal_handling', 'insight', 'medicine', 'nature', 'perception', 'religion', 'survival' ),
                'spellcasting'   => true,
                'spell_ability'  => 'wis',
                'starting_hp'    => 8,
                'subclass_level' => 2,
                'subclass_name'  => 'Círculo Druídico',
                'features' => array(
                    1 => array( 'Conjuração', 'Druídico' ),
                    2 => array( 'Forma Selvagem', 'Círculo Druídico' ),
                ),
            ),
            'fighter' => array(
                'name'           => 'Guerreiro',
                'hit_die'        => 10,
                'primary_ability'=> 'str',
                'saving_throws'  => array( 'str', 'con' ),
                'armor_prof'     => array( 'Todas', 'Escudos' ),
                'weapon_prof'    => array( 'Simples', 'Marciais' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'acrobatics', 'animal_handling', 'athletics', 'history', 'insight', 'intimidation', 'perception', 'survival' ),
                'starting_hp'    => 10,
                'subclass_level' => 3,
                'subclass_name'  => 'Arquétipo Marcial',
                'features' => array(
                    1 => array( 'Estilo de Luta', 'Retomar o Fôlego' ),
                    2 => array( 'Surto de Ação' ),
                    3 => array( 'Arquétipo Marcial' ),
                    5 => array( 'Ataque Extra' ),
                ),
            ),
            'monk' => array(
                'name'           => 'Monge',
                'hit_die'        => 8,
                'primary_ability'=> 'dex',
                'saving_throws'  => array( 'str', 'dex' ),
                'armor_prof'     => array(),
                'weapon_prof'    => array( 'Simples', 'Espadas curtas' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'acrobatics', 'athletics', 'history', 'insight', 'religion', 'stealth' ),
                'starting_hp'    => 8,
                'subclass_level' => 3,
                'subclass_name'  => 'Tradição Monástica',
                'features' => array(
                    1 => array( 'Defesa sem Armadura', 'Artes Marciais' ),
                    2 => array( 'Ki', 'Movimento sem Armadura' ),
                    3 => array( 'Tradição Monástica', 'Desviar Projéteis' ),
                    5 => array( 'Ataque Extra', 'Ataque Atordoante' ),
                ),
            ),
            'paladin' => array(
                'name'           => 'Paladino',
                'hit_die'        => 10,
                'primary_ability'=> 'str',
                'saving_throws'  => array( 'wis', 'cha' ),
                'armor_prof'     => array( 'Todas', 'Escudos' ),
                'weapon_prof'    => array( 'Simples', 'Marciais' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'athletics', 'insight', 'intimidation', 'medicine', 'persuasion', 'religion' ),
                'spellcasting'   => true,
                'spell_ability'  => 'cha',
                'starting_hp'    => 10,
                'subclass_level' => 3,
                'subclass_name'  => 'Juramento Sagrado',
                'features' => array(
                    1 => array( 'Sentido Divino', 'Cura pelas Mãos' ),
                    2 => array( 'Estilo de Luta', 'Conjuração', 'Destruição Divina' ),
                    3 => array( 'Juramento Sagrado', 'Canalizar Divindade' ),
                    5 => array( 'Ataque Extra' ),
                ),
            ),
            'ranger' => array(
                'name'           => 'Patrulheiro',
                'hit_die'        => 10,
                'primary_ability'=> 'dex',
                'saving_throws'  => array( 'str', 'dex' ),
                'armor_prof'     => array( 'Leve', 'Média', 'Escudos' ),
                'weapon_prof'    => array( 'Simples', 'Marciais' ),
                'skill_choices'  => 3,
                'skill_list'     => array( 'animal_handling', 'athletics', 'insight', 'investigation', 'nature', 'perception', 'stealth', 'survival' ),
                'spellcasting'   => true,
                'spell_ability'  => 'wis',
                'starting_hp'    => 10,
                'subclass_level' => 3,
                'subclass_name'  => 'Arquétipo de Patrulheiro',
                'features' => array(
                    1 => array( 'Inimigo Favorecido', 'Explorador Natural' ),
                    2 => array( 'Estilo de Luta', 'Conjuração' ),
                    3 => array( 'Arquétipo de Patrulheiro', 'Consciência Primitiva' ),
                    5 => array( 'Ataque Extra' ),
                ),
            ),
            'rogue' => array(
                'name'           => 'Ladino',
                'hit_die'        => 8,
                'primary_ability'=> 'dex',
                'saving_throws'  => array( 'dex', 'int' ),
                'armor_prof'     => array( 'Leve' ),
                'weapon_prof'    => array( 'Simples', 'Bestas de mão', 'Espadas longas', 'Rapieiras', 'Espadas curtas' ),
                'skill_choices'  => 4,
                'skill_list'     => array( 'acrobatics', 'athletics', 'deception', 'insight', 'intimidation', 'investigation', 'perception', 'performance', 'persuasion', 'sleight_of_hand', 'stealth' ),
                'starting_hp'    => 8,
                'subclass_level' => 3,
                'subclass_name'  => 'Arquétipo de Ladino',
                'features' => array(
                    1 => array( 'Especialização', 'Ataque Furtivo (1d6)', 'Gíria de Ladrão' ),
                    2 => array( 'Ação Ardilosa' ),
                    3 => array( 'Arquétipo de Ladino' ),
                    5 => array( 'Ataque Furtivo (3d6)', 'Esquiva Sobrenatural' ),
                ),
            ),
            'sorcerer' => array(
                'name'           => 'Feiticeiro',
                'hit_die'        => 6,
                'primary_ability'=> 'cha',
                'saving_throws'  => array( 'con', 'cha' ),
                'armor_prof'     => array(),
                'weapon_prof'    => array( 'Adagas', 'Dardos', 'Fundas', 'Bordões', 'Bestas leves' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'arcana', 'deception', 'insight', 'intimidation', 'persuasion', 'religion' ),
                'spellcasting'   => true,
                'spell_ability'  => 'cha',
                'starting_hp'    => 6,
                'subclass_level' => 1,
                'subclass_name'  => 'Origem de Feitiçaria',
                'features' => array(
                    1 => array( 'Conjuração', 'Origem de Feitiçaria' ),
                    2 => array( 'Fonte de Magia', 'Pontos de Feitiçaria' ),
                    3 => array( 'Metamagia' ),
                ),
            ),
            'warlock' => array(
                'name'           => 'Bruxo',
                'hit_die'        => 8,
                'primary_ability'=> 'cha',
                'saving_throws'  => array( 'wis', 'cha' ),
                'armor_prof'     => array( 'Leve' ),
                'weapon_prof'    => array( 'Simples' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'arcana', 'deception', 'history', 'intimidation', 'investigation', 'nature', 'religion' ),
                'spellcasting'   => true,
                'spell_ability'  => 'cha',
                'starting_hp'    => 8,
                'subclass_level' => 1,
                'subclass_name'  => 'Patrono Transcendental',
                'features' => array(
                    1 => array( 'Patrono Transcendental', 'Magia de Pacto' ),
                    2 => array( 'Invocações Místicas' ),
                    3 => array( 'Dádiva do Pacto' ),
                ),
            ),
            'wizard' => array(
                'name'           => 'Mago',
                'hit_die'        => 6,
                'primary_ability'=> 'int',
                'saving_throws'  => array( 'int', 'wis' ),
                'armor_prof'     => array(),
                'weapon_prof'    => array( 'Adagas', 'Dardos', 'Fundas', 'Bordões', 'Bestas leves' ),
                'skill_choices'  => 2,
                'skill_list'     => array( 'arcana', 'history', 'insight', 'investigation', 'medicine', 'religion' ),
                'spellcasting'   => true,
                'spell_ability'  => 'int',
                'starting_hp'    => 6,
                'subclass_level' => 2,
                'subclass_name'  => 'Tradição Arcana',
                'features' => array(
                    1 => array( 'Conjuração', 'Recuperação Arcana' ),
                    2 => array( 'Tradição Arcana' ),
                ),
            ),
        );
    }

    // =============================================
    // BACKGROUNDS (Antecedentes)
    // =============================================
    public static function get_backgrounds() {
        return array(
            'acolyte'        => array( 'name' => 'Acólito', 'skills' => array( 'insight', 'religion' ), 'languages' => 2 ),
            'charlatan'      => array( 'name' => 'Charlatão', 'skills' => array( 'deception', 'sleight_of_hand' ), 'tools' => array( 'Kit de disfarce', 'Kit de falsificação' ) ),
            'criminal'       => array( 'name' => 'Criminoso', 'skills' => array( 'deception', 'stealth' ), 'tools' => array( 'Ferramentas de ladrão', 'Kit de jogo' ) ),
            'entertainer'    => array( 'name' => 'Artista', 'skills' => array( 'acrobatics', 'performance' ), 'tools' => array( 'Kit de disfarce', 'Instrumento musical' ) ),
            'folk_hero'      => array( 'name' => 'Herói do Povo', 'skills' => array( 'animal_handling', 'survival' ), 'tools' => array( 'Ferramentas de artesão', 'Veículos terrestres' ) ),
            'guild_artisan'  => array( 'name' => 'Artesão de Guilda', 'skills' => array( 'insight', 'persuasion' ), 'tools' => array( 'Ferramentas de artesão' ), 'languages' => 1 ),
            'hermit'         => array( 'name' => 'Eremita', 'skills' => array( 'medicine', 'religion' ), 'tools' => array( 'Kit de herbalismo' ), 'languages' => 1 ),
            'noble'          => array( 'name' => 'Nobre', 'skills' => array( 'history', 'persuasion' ), 'tools' => array( 'Kit de jogo' ), 'languages' => 1 ),
            'outlander'      => array( 'name' => 'Forasteiro', 'skills' => array( 'athletics', 'survival' ), 'tools' => array( 'Instrumento musical' ), 'languages' => 1 ),
            'sage'           => array( 'name' => 'Sábio', 'skills' => array( 'arcana', 'history' ), 'languages' => 2 ),
            'sailor'         => array( 'name' => 'Marinheiro', 'skills' => array( 'athletics', 'perception' ), 'tools' => array( 'Ferramentas de navegação', 'Veículos aquáticos' ) ),
            'soldier'        => array( 'name' => 'Soldado', 'skills' => array( 'athletics', 'intimidation' ), 'tools' => array( 'Kit de jogo', 'Veículos terrestres' ) ),
            'urchin'         => array( 'name' => 'Órfão', 'skills' => array( 'sleight_of_hand', 'stealth' ), 'tools' => array( 'Kit de disfarce', 'Ferramentas de ladrão' ) ),
        );
    }

    // =============================================
    // ALINHAMENTOS
    // =============================================
    public static function get_alignments() {
        return array(
            'LG' => 'Leal e Bom',
            'NG' => 'Neutro e Bom',
            'CG' => 'Caótico e Bom',
            'LN' => 'Leal e Neutro',
            'TN' => 'Verdadeiro Neutro',
            'CN' => 'Caótico e Neutro',
            'LE' => 'Leal e Mau',
            'NE' => 'Neutro e Mau',
            'CE' => 'Caótico e Mau',
        );
    }

    // =============================================
    // SKILLS (Perícias) e seus atributos base
    // =============================================
    public static function get_skills() {
        return array(
            'acrobatics'      => array( 'name' => 'Acrobacia', 'ability' => 'dex' ),
            'animal_handling' => array( 'name' => 'Adestrar Animais', 'ability' => 'wis' ),
            'arcana'          => array( 'name' => 'Arcanismo', 'ability' => 'int' ),
            'athletics'       => array( 'name' => 'Atletismo', 'ability' => 'str' ),
            'deception'       => array( 'name' => 'Enganação', 'ability' => 'cha' ),
            'history'         => array( 'name' => 'História', 'ability' => 'int' ),
            'insight'         => array( 'name' => 'Intuição', 'ability' => 'wis' ),
            'intimidation'    => array( 'name' => 'Intimidação', 'ability' => 'cha' ),
            'investigation'   => array( 'name' => 'Investigação', 'ability' => 'int' ),
            'medicine'        => array( 'name' => 'Medicina', 'ability' => 'wis' ),
            'nature'          => array( 'name' => 'Natureza', 'ability' => 'int' ),
            'perception'      => array( 'name' => 'Percepção', 'ability' => 'wis' ),
            'performance'     => array( 'name' => 'Atuação', 'ability' => 'cha' ),
            'persuasion'      => array( 'name' => 'Persuasão', 'ability' => 'cha' ),
            'religion'        => array( 'name' => 'Religião', 'ability' => 'int' ),
            'sleight_of_hand' => array( 'name' => 'Prestidigitação', 'ability' => 'dex' ),
            'stealth'         => array( 'name' => 'Furtividade', 'ability' => 'dex' ),
            'survival'        => array( 'name' => 'Sobrevivência', 'ability' => 'wis' ),
        );
    }

    // =============================================
    // CONDIÇÕES (Conditions)
    // =============================================
    public static function get_conditions() {
        return array(
            'blinded'       => array( 'name' => 'Cego', 'description' => 'Falha automaticamente qualquer teste que requeira visão. Ataques contra a criatura têm vantagem, ataques da criatura têm desvantagem.' ),
            'charmed'       => array( 'name' => 'Encantado', 'description' => 'Não pode atacar o encantador. O encantador tem vantagem em testes de interação social.' ),
            'deafened'      => array( 'name' => 'Surdo', 'description' => 'Falha automaticamente qualquer teste que requeira audição.' ),
            'frightened'    => array( 'name' => 'Amedrontado', 'description' => 'Desvantagem em testes de habilidade e ataques enquanto a fonte do medo estiver visível. Não pode se mover voluntariamente em direção à fonte.' ),
            'grappled'      => array( 'name' => 'Agarrado', 'description' => 'Velocidade reduzida a 0.' ),
            'incapacitated' => array( 'name' => 'Incapacitado', 'description' => 'Não pode realizar ações ou reações.' ),
            'invisible'     => array( 'name' => 'Invisível', 'description' => 'Impossível de ser visto sem magia. Ataques da criatura têm vantagem, ataques contra ela têm desvantagem.' ),
            'paralyzed'     => array( 'name' => 'Paralisado', 'description' => 'Incapacitado, não pode se mover ou falar. Falha em saves de FOR e DES. Ataques têm vantagem, ataques corpo a corpo são críticos automáticos.' ),
            'petrified'     => array( 'name' => 'Petrificado', 'description' => 'Transformado em substância sólida. Peso x10. Resistência a todos os danos. Imune a veneno e doença.' ),
            'poisoned'      => array( 'name' => 'Envenenado', 'description' => 'Desvantagem em jogadas de ataque e testes de habilidade.' ),
            'prone'         => array( 'name' => 'Caído', 'description' => 'Só pode rastejar. Desvantagem em ataques. Ataques a 5ft têm vantagem, a distância têm desvantagem.' ),
            'restrained'    => array( 'name' => 'Contido', 'description' => 'Velocidade 0. Ataques contra têm vantagem. Ataques da criatura e saves de DES têm desvantagem.' ),
            'stunned'       => array( 'name' => 'Atordoado', 'description' => 'Incapacitado, não pode se mover, fala balbuciada. Falha em saves de FOR e DES. Ataques contra têm vantagem.' ),
            'unconscious'   => array( 'name' => 'Inconsciente', 'description' => 'Incapacitado, não pode se mover ou falar, inconsciente. Cai no chão. Falha em saves de FOR e DES. Ataques têm vantagem, corpo a corpo = crítico.' ),
            'exhaustion_1'  => array( 'name' => 'Exaustão 1', 'description' => 'Desvantagem em testes de habilidade.' ),
            'exhaustion_2'  => array( 'name' => 'Exaustão 2', 'description' => 'Velocidade reduzida pela metade.' ),
            'exhaustion_3'  => array( 'name' => 'Exaustão 3', 'description' => 'Desvantagem em jogadas de ataque e salvamento.' ),
            'exhaustion_4'  => array( 'name' => 'Exaustão 4', 'description' => 'Pontos de vida máximos reduzidos pela metade.' ),
            'exhaustion_5'  => array( 'name' => 'Exaustão 5', 'description' => 'Velocidade reduzida a 0.' ),
            'exhaustion_6'  => array( 'name' => 'Exaustão 6', 'description' => 'Morte.' ),
        );
    }

    // =============================================
    // CÁLCULOS D&D 5e
    // =============================================

    /**
     * Calcula o modificador de atributo
     */
    public static function ability_modifier( $score ) {
        return intval( floor( ( $score - 10 ) / 2 ) );
    }

    /**
     * Bônus de proficiência por nível
     */
    public static function proficiency_bonus( $level ) {
        if ( $level >= 17 ) return 6;
        if ( $level >= 13 ) return 5;
        if ( $level >= 9 ) return 4;
        if ( $level >= 5 ) return 3;
        return 2;
    }

    /**
     * Tabela de XP por nível
     */
    public static function xp_for_level( $level ) {
        $table = array(
            1 => 0, 2 => 300, 3 => 900, 4 => 2700, 5 => 6500,
            6 => 14000, 7 => 23000, 8 => 34000, 9 => 48000, 10 => 64000,
            11 => 85000, 12 => 100000, 13 => 120000, 14 => 140000, 15 => 165000,
            16 => 195000, 17 => 225000, 18 => 265000, 19 => 305000, 20 => 355000,
        );
        return isset( $table[ $level ] ) ? $table[ $level ] : 0;
    }

    /**
     * Calcula o nível baseado no XP
     */
    public static function level_from_xp( $xp ) {
        for ( $level = 20; $level >= 1; $level-- ) {
            if ( $xp >= self::xp_for_level( $level ) ) {
                return $level;
            }
        }
        return 1;
    }

    /**
     * XP de monstros por CR
     */
    public static function xp_by_cr( $cr ) {
        $table = array(
            '0' => 10, '1/8' => 25, '1/4' => 50, '1/2' => 100,
            '1' => 200, '2' => 450, '3' => 700, '4' => 1100, '5' => 1800,
            '6' => 2300, '7' => 2900, '8' => 3900, '9' => 5000, '10' => 5900,
            '11' => 7200, '12' => 8400, '13' => 10000, '14' => 11500, '15' => 13000,
            '16' => 15000, '17' => 18000, '18' => 20000, '19' => 22000, '20' => 25000,
            '21' => 33000, '22' => 41000, '23' => 50000, '24' => 62000, '25' => 75000,
            '26' => 90000, '27' => 105000, '28' => 120000, '29' => 135000, '30' => 155000,
        );
        return isset( $table[ (string) $cr ] ) ? $table[ (string) $cr ] : 0;
    }

    /**
     * Calcula HP máximo do personagem
     * Nível 1: HP máximo do dado de vida + CON modifier
     * Demais: média do dado de vida + CON modifier por nível
     */
    public static function calculate_max_hp( $class_key, $level, $con_score ) {
        $classes = self::get_classes();
        if ( ! isset( $classes[ $class_key ] ) ) return 0;

        $hit_die = $classes[ $class_key ]['hit_die'];
        $con_mod = self::ability_modifier( $con_score );

        // Nível 1: valor máximo do dado
        $hp = $hit_die + $con_mod;

        // Níveis seguintes: média do dado + CON mod
        $avg_roll = floor( $hit_die / 2 ) + 1;
        for ( $i = 2; $i <= $level; $i++ ) {
            $hp += $avg_roll + $con_mod;
        }

        return max( 1, $hp );
    }

    /**
     * Calcula Armor Class base
     */
    public static function calculate_base_ac( $dex_score, $armor_type = 'none', $has_shield = false ) {
        $dex_mod = self::ability_modifier( $dex_score );
        $ac = 10;

        switch ( $armor_type ) {
            // Leve
            case 'padded':
            case 'leather':
                $ac = 11 + $dex_mod;
                break;
            case 'studded_leather':
                $ac = 12 + $dex_mod;
                break;
            // Média
            case 'hide':
                $ac = 12 + min( $dex_mod, 2 );
                break;
            case 'chain_shirt':
                $ac = 13 + min( $dex_mod, 2 );
                break;
            case 'scale_mail':
                $ac = 14 + min( $dex_mod, 2 );
                break;
            case 'breastplate':
                $ac = 14 + min( $dex_mod, 2 );
                break;
            case 'half_plate':
                $ac = 15 + min( $dex_mod, 2 );
                break;
            // Pesada
            case 'ring_mail':
                $ac = 14;
                break;
            case 'chain_mail':
                $ac = 16;
                break;
            case 'splint':
                $ac = 17;
                break;
            case 'plate':
                $ac = 18;
                break;
            // Sem armadura
            default:
                $ac = 10 + $dex_mod;
                break;
        }

        if ( $has_shield ) {
            $ac += 2;
        }

        return $ac;
    }

    /**
     * Calcula o bônus de skill
     */
    public static function skill_bonus( $ability_score, $proficiency_level, $proficiency_bonus ) {
        $mod = self::ability_modifier( $ability_score );
        
        if ( $proficiency_level === 2 ) {
            // Expertise
            return $mod + ( $proficiency_bonus * 2 );
        } elseif ( $proficiency_level === 1 ) {
            // Proficiente
            return $mod + $proficiency_bonus;
        }
        
        return $mod;
    }

    /**
     * Calcula Passive Perception
     */
    public static function passive_perception( $wis_score, $perception_prof, $proficiency_bonus ) {
        return 10 + self::skill_bonus( $wis_score, $perception_prof, $proficiency_bonus );
    }

    /**
     * Retorna os spell slots por nível de conjurador
     */
    public static function spell_slots( $caster_level ) {
        $table = array(
            1  => array( 2, 0, 0, 0, 0, 0, 0, 0, 0 ),
            2  => array( 3, 0, 0, 0, 0, 0, 0, 0, 0 ),
            3  => array( 4, 2, 0, 0, 0, 0, 0, 0, 0 ),
            4  => array( 4, 3, 0, 0, 0, 0, 0, 0, 0 ),
            5  => array( 4, 3, 2, 0, 0, 0, 0, 0, 0 ),
            6  => array( 4, 3, 3, 0, 0, 0, 0, 0, 0 ),
            7  => array( 4, 3, 3, 1, 0, 0, 0, 0, 0 ),
            8  => array( 4, 3, 3, 2, 0, 0, 0, 0, 0 ),
            9  => array( 4, 3, 3, 3, 1, 0, 0, 0, 0 ),
            10 => array( 4, 3, 3, 3, 2, 0, 0, 0, 0 ),
            11 => array( 4, 3, 3, 3, 2, 1, 0, 0, 0 ),
            12 => array( 4, 3, 3, 3, 2, 1, 0, 0, 0 ),
            13 => array( 4, 3, 3, 3, 2, 1, 1, 0, 0 ),
            14 => array( 4, 3, 3, 3, 2, 1, 1, 0, 0 ),
            15 => array( 4, 3, 3, 3, 2, 1, 1, 1, 0 ),
            16 => array( 4, 3, 3, 3, 2, 1, 1, 1, 0 ),
            17 => array( 4, 3, 3, 3, 2, 1, 1, 1, 1 ),
            18 => array( 4, 3, 3, 3, 3, 1, 1, 1, 1 ),
            19 => array( 4, 3, 3, 3, 3, 2, 1, 1, 1 ),
            20 => array( 4, 3, 3, 3, 3, 2, 2, 1, 1 ),
        );
        return isset( $table[ $caster_level ] ) ? $table[ $caster_level ] : array( 0, 0, 0, 0, 0, 0, 0, 0, 0 );
    }

    /**
     * Retorna dados completos para a ficha do personagem
     */
    public static function build_character_sheet_data( $character ) {
        $classes = self::get_classes();
        $races = self::get_races();
        $skills = self::get_skills();
        
        $class_data = isset( $classes[ $character->class ] ) ? $classes[ $character->class ] : null;
        $race_data = isset( $races[ $character->race ] ) ? $races[ $character->race ] : null;

        $prof_bonus = self::proficiency_bonus( $character->level );

        // Modificadores
        $mods = array(
            'str' => self::ability_modifier( $character->strength ),
            'dex' => self::ability_modifier( $character->dexterity ),
            'con' => self::ability_modifier( $character->constitution ),
            'int' => self::ability_modifier( $character->intelligence ),
            'wis' => self::ability_modifier( $character->wisdom ),
            'cha' => self::ability_modifier( $character->charisma ),
        );

        // Saving throws
        $saves = array();
        $save_fields = array( 'str' => 'save_str', 'dex' => 'save_dex', 'con' => 'save_con', 'int' => 'save_int', 'wis' => 'save_wis', 'cha' => 'save_cha' );
        foreach ( $save_fields as $ability => $field ) {
            $is_prof = intval( $character->$field );
            $saves[ $ability ] = array(
                'modifier'   => $mods[ $ability ] + ( $is_prof ? $prof_bonus : 0 ),
                'proficient' => $is_prof,
            );
        }

        // Skills
        $skill_bonuses = array();
        foreach ( $skills as $skill_key => $skill_info ) {
            $field = 'skill_' . $skill_key;
            $prof_level = intval( $character->$field );
            $ability = $skill_info['ability'];
            $skill_bonuses[ $skill_key ] = array(
                'name'      => $skill_info['name'],
                'ability'   => $ability,
                'modifier'  => self::skill_bonus( $character->{self::ability_name( $ability )}, $prof_level, $prof_bonus ),
                'proficiency' => $prof_level,
            );
        }

        // XP para próximo nível
        $next_level = min( $character->level + 1, 20 );
        $xp_next = self::xp_for_level( $next_level );

        return array(
            'character'       => $character,
            'class_data'      => $class_data,
            'race_data'       => $race_data,
            'modifiers'       => $mods,
            'proficiency_bonus' => $prof_bonus,
            'saving_throws'   => $saves,
            'skills'          => $skill_bonuses,
            'passive_perception' => self::passive_perception( $character->wisdom, $character->skill_perception, $prof_bonus ),
            'xp_next_level'   => $xp_next,
            'xp_progress'     => $character->level < 20 ? round( ( $character->experience_points - self::xp_for_level( $character->level ) ) / max( 1, $xp_next - self::xp_for_level( $character->level ) ) * 100 ) : 100,
            'initiative'      => $mods['dex'] + $character->initiative_bonus,
        );
    }

    /**
     * Helper: retorna o nome completo do atributo
     */
    private static function ability_name( $short ) {
        $map = array(
            'str' => 'strength',
            'dex' => 'dexterity',
            'con' => 'constitution',
            'int' => 'intelligence',
            'wis' => 'wisdom',
            'cha' => 'charisma',
        );
        return isset( $map[ $short ] ) ? $map[ $short ] : $short;
    }
}
