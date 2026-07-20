import type { AcademicPathway, AcademicPathwayLevel, Program } from '../types';

export const programPathways = (program: Program): AcademicPathway[] =>
    program.mention_record?.parcours ?? [];

export const pathwayLevels = (pathway: AcademicPathway): AcademicPathwayLevel[] =>
    [...(pathway.level_links ?? [])]
        .filter((link) => link.is_active !== false && link.level)
        .sort((left, right) => (left.level?.ordre ?? left.level?.id ?? 0) - (right.level?.ordre ?? right.level?.id ?? 0));

export const programLevelLabel = (program: Program): string => {
    const levels = [...new Set(programPathways(program).flatMap((pathway) => pathwayLevels(pathway).map((link) => link.level?.code)).filter(Boolean))];

    return levels.join(' · ') || program.level;
};

export const programPathwayLabel = (program: Program): string =>
    programPathways(program).map((pathway) => pathway.nom).join(' · ') || program.track || '';
