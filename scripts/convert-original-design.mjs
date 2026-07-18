import fs from 'node:fs';

const source = fs.readFileSync('resources/design-reference/EDSP Site.dc.html', 'utf8');
let html = source.match(/<x-dc>([\s\S]*?)<script[^>]*>/)?.[1] ?? '';
html = html.replace(/<helmet>[\s\S]*?<\/helmet>/, '');
html = html.replace(/<sc-if value="\{\{ isDesktop \}\}"[^>]*>/g, '<template v-if="desktop">');
html = html.replace(/<sc-if value="\{\{ isMobile \}\}"[^>]*>/g, '<template v-if="!desktop">');
html = html.replace(/<sc-if value="\{\{ menuOpen \}\}"[^>]*>/g, '<template v-if="menuOpen">');
html = html.replace(/<sc-if value="\{\{ dropEcole \}\}"[^>]*>/g, '<template v-if="dropEcole">');
html = html.replace(/<sc-if value="\{\{ dropFormations \}\}"[^>]*>/g, '<template v-if="dropFormations">');
html = html.replace(/<sc-if value="\{\{ showTop \}\}"[^>]*>/g, '<template v-if="showTop">');
html = html.replace(/<\/sc-if>/g, '</template>');
html = html.replace(/<sc-for list="\{\{ actualites \}\}" as="actu"[^>]*>/g, '<template v-for="actu in actualites" :key="actu.titre">');
html = html.replace(/<sc-for list="\{\{ equipe \}\}" as="membre"[^>]*>/g, '<template v-for="membre in equipe" :key="membre.fonction">');
html = html.replace(/<sc-for list="\{\{ temoignages \}\}" as="temoin"[^>]*>/g, '<template v-for="temoin in temoignages" :key="temoin.nom">');
html = html.replace(/<\/sc-for>/g, '</template>');
html = html.replace(/<\/x-dc>/g, '');
html = html.replace(/<x-import[^>]*placeholder="([^"]*)"[^>]*><\/x-import>/g, '<div class="design-image" role="img" aria-label="$1"><img src="/images/logo-edsp.png" alt=""></div>');
html = html.replace(/\sstyle-hover="[^"]*"/g, '');
html = html.replace(/onClick="\{\{ ([^}]+) \}\}"/g, '@click="$1"');
html = html.replace(/onMouseEnter="\{\{ ([^}]+) \}\}"/g, '@mouseenter="$1"');
html = html.replace(/onMouseLeave="\{\{ ([^}]+) \}\}"/g, '@mouseleave="$1"');
html = html.replace(/aria-expanded="\{\{ ([^}]+) \}\}"/g, ':aria-expanded="$1"');
html = html.replace(/style="[^"]*\{\{[^"}]*\}\}[^"}]*"/g, '');
html = html.replace(/href="#/g, 'href="/#');

const setup = `<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
const width=ref(window.innerWidth),menuOpen=ref(false),drop=ref<string|null>(null),showTop=ref(false);const desktop=ref(width.value>=1120);const dropEcole=ref(false),dropFormations=ref(false);
const resize=()=>{width.value=window.innerWidth;desktop.value=width.value>=1120;if(desktop.value)menuOpen.value=false};const scroll=()=>showTop.value=window.scrollY>500;
onMounted(()=>{window.addEventListener('resize',resize);window.addEventListener('scroll',scroll,{passive:true})});onBeforeUnmount(()=>{window.removeEventListener('resize',resize);window.removeEventListener('scroll',scroll)});
const toggleMenu=()=>menuOpen.value=!menuOpen.value,closeMenu=()=>menuOpen.value=false,openEcole=()=>dropEcole.value=true,openFormations=()=>dropFormations.value=true,closeDrops=()=>{dropEcole.value=false;dropFormations.value=false},toggleEcole=()=>dropEcole.value=!dropEcole.value,toggleFormations=()=>dropFormations.value=!dropFormations.value,scrollTop=()=>window.scrollTo({top:0,behavior:'smooth'});
const actualites=[{categorie:'Admissions',date:'Date à publier',titre:'Ouverture des préinscriptions',resume:'L’avis officiel précisant le calendrier, les pièces à fournir et les modalités sera publié dans cette rubrique.'},{categorie:'Vie académique',date:'Date à publier',titre:'Calendrier académique',resume:'Retrouvez les dates clés de l’année universitaire.'},{categorie:'Recherche',date:'Date à publier',titre:'Activités scientifiques et conférences',resume:'Conférences, journées d’étude et rencontres académiques organisées par l’EDSP.'}];
const equipe=[{categorie:'Direction',nom:'[Nom du directeur]',fonction:'Directeur de l’EDSP',bio:'Présentation à compléter avec les informations officielles.'},{categorie:'Administration',nom:'[Nom du responsable]',fonction:'Responsable administratif',bio:'Accueil, scolarité et suivi administratif.'},{categorie:'Enseignants',nom:'[Nom de l’enseignant]',fonction:'Enseignant-chercheur en droit privé',bio:'Domaines d’enseignement et de recherche.'},{categorie:'Responsables pédagogiques',nom:'[Nom du responsable]',fonction:'Responsable du parcours Science Politique',bio:'Coordination pédagogique du parcours.'}];
const temoignages=[{nom:'Miora R. (exemple)',parcours:'Licence 3 — Droit Privé',citation:'Les enseignants nous poussent à raisonner comme de vrais juristes.'},{nom:'Tojo A. (exemple)',parcours:'Master 1 — Science Politique',citation:'Les débats ouvrent les yeux sur les enjeux de gouvernance.'},{nom:'Fanja H. (exemple)',parcours:'Licence 2 — Droit Privé',citation:'La proximité avec les enseignants fait la différence.'}];
const campagneMessage='Campagne de préinscription : consultez l’avis officiel en cours.';
</script>`;
const css=`<style scoped>.design-image{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef2f7,#dce5f2)}.design-image img{max-width:46%;max-height:70%;object-fit:contain} @media(max-width:700px){section{overflow:hidden}h1{font-size:38px!important}h2{font-size:28px!important}}</style>`;
fs.writeFileSync('resources/js/pages/Home.vue', `${setup}\n<template>${html}</template>\n${css}\n`);
