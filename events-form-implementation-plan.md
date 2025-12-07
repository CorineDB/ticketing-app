Enhanced Event Form - Implementation Plan (Révisé)
Vue d'Ensemble
Transformer 
EventFormView.vue
 en une expérience utilisateur riche inspirée de WordPress, avec une interface à deux colonnes (principale + sidebar) pour la création/édition d'événements.

Objectifs Clés
Interface WordPress-Style - Layout deux colonnes (contenu principal + sidebar)
Gestion des Gates - Portes avec types, statuts, horaires et assignation
Création Inline d'Agents - Créer un agent directement lors de l'assignation
Gates par Type de Ticket - Option pour auto-créer des gates par type de ticket
Médias Riches - Upload d'images multiples avec galerie
Statuts Avancés - Gestion des statuts d'événement, gates et agents
Architecture Proposée - Hybrid WordPress + Steps
Structure Deux Colonnes avec Steps
┌─────────────────────────────────────────────────────────────┐
│                     Event Form Header                        │
│  [Title Input - Large]                                       │
└─────────────────────────────────────────────────────────────┘
┌──────────────────────────────┬──────────────────────────────┐
│   SECTION PRINCIPALE (70%)   │   SIDEBAR SECONDAIRE (30%)   │
├──────────────────────────────┼──────────────────────────────┤
│                              │ ┌──────────────────────────┐ │
│ [Step Progress Indicator]    │ │ 📊 Publication           │ │
│ ● ○ ○ ○                      │ │ • Status Selector        │ │
│                              │ │ • Publish Date           │ │
│ ┌──────────────────────────┐ │ │ • Schedule Options       │ │
│ │ STEP 1: Description      │ │ └──────────────────────────┘ │
│ │ [Rich Text Editor]       │ │                              │
│ └──────────────────────────┘ │ ┌──────────────────────────┐ │
│                              │ │ 📍 Location              │ │
│ ┌──────────────────────────┐ │ │ • Location (text)        │ │
│ │ STEP 2: Tickets          │ │ │ • Capacity               │ │
│ │ [Ticket Types Manager]   │ │ │ • Dress Code             │ │
│ │ ☑️ Auto-create gates     │ │ │ • Allow Re-entry         │ │
│ └──────────────────────────┘ │ └──────────────────────────┘ │
│                              │                              │
│ ┌──────────────────────────┐ │ ┌──────────────────────────┐ │
│ │ STEP 3: Gates            │ │ │ 🔗 Social Links          │ │
│ │ [Gates Manager]          │ │ │ • Facebook               │ │
│ └──────────────────────────┘ │ │ • Instagram              │ │
│                              │ │ • Twitter                │ │
│ ┌──────────────────────────┐ │ └──────────────────────────┘ │
│ │ STEP 4: Médias           │ │                              │
│ │ [Image Gallery]          │ │ ┌──────────────────────────┐ │
│ └──────────────────────────┘ │ │ 📋 Actions               │ │
│                              │ │ • Save Draft             │ │
│ [Previous] [Next/Save]       │ │ • Publish                │ │
│                              │ │ • Schedule               │ │
│                              │ └──────────────────────────┘ │
└──────────────────────────────┴──────────────────────────────┘
Navigation par Steps (Section Principale)
Step 1: Description

Rich text editor pour description détaillée
Dates de début et fin
Step 2: Types de Tickets

Gestion complète des tickets
Option "Auto-create gates par type"
Step 3: Gates

Configuration des portes
Statuts et horaires
Assignation agents
Step 4: Médias

Upload banner + galerie
Sidebar Fixe (Toujours Visible)
Publication - Statut, date, scheduling
Location - Location (texte libre), capacité, options
Social Links - Liens réseaux sociaux
Actions - Boutons save/publish
Nouvelles Fonctionnalités Clés
1. Création Inline d'Agent ⭐
Lors de l'assignation d'un agent à une gate, si l'agent n'existe pas:

<AgentSelector v-model="gate.agent_id">
  <template #no-results>
    <button @click="showCreateAgentModal = true">
      + Créer un nouvel agent
    </button>
  </template>
</AgentSelector>
<CreateAgentModal
  v-model="showCreateAgentModal"
  @created="assignNewAgent"
/>
Champs pour création rapide d'agent:

Nom complet
Email
Téléphone
Photo (optionnel)
Assignation immédiate à la gate
2. Auto-création de Gates par Type de Ticket ⭐
Dans la section Types de Tickets:

<div class="ticket-type-options">
  <label>
    <input type="checkbox" v-model="autoCreateGates" />
    Créer automatiquement une gate par type de ticket
  </label>
</div>
Comportement:

Si activé: crée une gate pour chaque type de ticket
Nom auto: "Gate {Ticket Type Name}"
Type: "entry" par défaut
Ticket type assigné automatiquement
3. Gestion des Statuts Avancée ⭐
Statuts d'Événement
draft, published, ongoing, completed, cancelled
Statuts de Gate
interface GateStatus {
  operational_status: 'active' | 'inactive' | 'paused'
  schedule: {
    start_time: string  // HH:mm
    end_time: string    // HH:mm
    days: string[]      // ['monday', 'tuesday', ...]
  }
}
Statuts d'Agent
interface AgentStatus {
  availability: 'active' | 'inactive' | 'on_break'
  shift_start?: string
  shift_end?: string
}
UI pour statuts:

Badges colorés (🟢 Active, 🔴 Inactive, 🟡 Paused)
Toggle rapide pour changer statut
Indicateur d'horaires actifs
Composants à Créer/Modifier
1. [MODIFY] 
EventFormView.vue
Layout WordPress-Style:

<template>
  <DashboardLayout>
    <div class="event-form-container">
      <!-- Header avec titre -->
      <div class="form-header">
        <input 
          v-model="formData.title" 
          type="text"
          placeholder="Titre de l'événement"
          class="title-input-large"
        />
      </div>
      <!-- Layout deux colonnes -->
      <div class="two-column-layout">
        <!-- Colonne principale (70%) -->
        <div class="main-content">
          <DescriptionEditor v-model="formData.description" />
          <TicketTypesSection 
            v-model="formData.ticket_types"
            @auto-create-gates="handleAutoCreateGates"
          />
          <GatesSection 
            v-model="formData.gates"
            :ticket-types="formData.ticket_types"
          />
          <MediaGallery v-model="formData.images" />
        </div>
        <!-- Sidebar secondaire (30%) -->
        <div class="sidebar-content">
          <PublicationPanel v-model:status="formData.status" />
          <LocationPanel v-model="formData.location" />
          <SocialLinksPanel v-model="formData.social_links" />
          <ReviewActionsPanel @save-draft="saveDraft" @publish="publish" />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
```(file:///home/pc-1/workspaces/ticketing-app/ticketing-app/src/views/Events/EventFormView.vue)
**Transformation majeure:**
- Remplacer le formulaire simple par un wizard multi-étapes
- Ajouter navigation entre étapes avec indicateur de progression
- Implémenter sauvegarde automatique (draft)
- Gérer les états de validation par étape
**Nouvelles sections:**
```vue
<template>
  <DashboardLayout>
    <!-- Progress Stepper -->
    <StepProgress :current="currentStep" :total="7" />
    
    <!-- Step Content -->
    <div class="step-container">
      <component :is="currentStepComponent" v-model="formData" />
    </div>
    
    <!-- Navigation -->
    <StepNavigation 
      @previous="previousStep" 
      @next="nextStep"
      @save-draft="saveDraft"
      @publish="publishEvent"
    />
  </DashboardLayout>
</template>
2. [NEW] Composants Sidebar
PublicationPanel.vue
<div class="panel">
  <h3>Publication</h3>
  
  <!-- Status Selector -->
  <StatusBadgeSelector 
    v-model="status"
    :statuses="['draft', 'published', 'ongoing', 'completed', 'cancelled']"
  />
  
  <!-- Publish Date -->
  <div class="field">
    <label>Date de publication</label>
    <input type="datetime-local" v-model="publishDate" />
  </div>
  
  <!-- Schedule Options -->
  <div class="field">
    <label>
      <input type="checkbox" v-model="schedulePublish" />
      Programmer la publication
    </label>
  </div>
</div>
LocationPanel.vue
<div class="panel">
  <h3>Localisation & Capacité</h3>
  
  <!-- Note: Backend uses single 'location' field -->
  <textarea 
    v-model="location.location" 
    placeholder="Lieu de l'événement (adresse complète)"
    rows="3"
  ></textarea>
  
  <input 
    v-model.number="location.capacity" 
    type="number" 
    placeholder="Capacité totale"
    min="1"
  />
  
  <label>
    <input type="checkbox" v-model="location.allow_reentry" />
    Autoriser la re-entrée
  </label>
  
  <input v-model="location.dress_code" placeholder="Dress code (optionnel)" />
</div>
SocialLinksPanel.vue
<div class="panel">
  <h3>Réseaux Sociaux</h3>
  
  <div v-for="platform in platforms" class="social-link-field">
    <component :is="platform.icon" class="icon" />
    <input 
      v-model="socialLinks[platform.key]"
      :placeholder="`Lien ${platform.name}`"
      type="url"
    />
  </div>
</div>
ReviewActionsPanel.vue
<div class="panel actions-panel">
  <h3>Actions</h3>
  
  <button @click="$emit('save-draft')" class="btn-secondary">
    💾 Sauvegarder brouillon
  </button>
  
  <button @click="$emit('publish')" class="btn-primary">
    🚀 Publier l'événement
  </button>
  
  <button v-if="canSchedule" @click="$emit('schedule')" class="btn-outline">
    📅 Programmer
  </button>
</div>
3. [NEW] Step Components (Main Content)
Step1Description.vue
<div class="step-content">
  <h2>📝 Description de l'événement</h2>
  
  <!-- Rich Text Editor -->
  <RichTextEditor v-model="description" />
  
  <!-- Dates -->
  <div class="date-fields">
    <div class="field">
      <label>Date et heure de début *</label>
      <input type="datetime-local" v-model="start_datetime" required />
    </div>
    
    <div class="field">
      <label>Date et heure de fin</label>
      <input type="datetime-local" v-model="end_datetime" />
    </div>
  </div>
</div>
Step2TicketTypes.vue
<div class="step-content">
  <div class="step-header">
    <h2>🎫 Types de Tickets</h2>
    <button @click="addTicketType">+ Ajouter</button>
  </div>
  
  <!-- Option auto-create gates -->
  <div class="auto-gates-option">
    <label>
      <input type="checkbox" v-model="autoCreateGates" />
      Créer automatiquement une gate par type de ticket
    </label>
    <p class="help-text">
      Une porte sera créée pour chaque type de ticket avec assignation automatique
    </p>
  </div>
  
  <!-- Ticket Types List -->
  <TicketTypeCard
    v-for="ticket in ticketTypes"
    :key="ticket.id"
    :ticket="ticket"
    @edit="editTicket"
    @delete="deleteTicket"
  />
</div>
Step3Gates.vue
<div class="step-content">
  <div class="step-header">
    <h2>🚪 Gates (Portes)</h2>
    <button @click="addGate">+ Ajouter une gate</button>
  </div>
  
  <GateCard
    v-for="gate in gates"
    :key="gate.id"
    :gate="gate"
    :ticket-types="ticketTypes"
    @edit="editGate"
    @delete="deleteGate"
    @toggle-status="toggleGateStatus"
  />
  
  <GateFormModal
    v-model="showGateModal"
    :gate="selectedGate"
    :ticket-types="ticketTypes"
    @submit="saveGate"
  />
</div>
Step4Media.vue
<div class="step-content">
  <h2>🖼️ Médias</h2>
  
  <!-- Banner principal -->
  <div class="banner-upload">
    <h3>Image principale (Banner)</h3>
    <ImageUploader
      v-model="banner"
      :max-size="5242880"
      recommended-size="1200x600"
      @upload="handleBannerUpload"
    />
  </div>
  
  <!-- Galerie secondaire -->
  <div class="gallery-upload">
    <h3>Galerie d'images (max 5)</h3>
    <MultiImageUploader
      v-model="gallery"
      :max-files="5"
      :max-size="5242880"
      @upload="handleGalleryUpload"
    />
  </div>
</div>
4. [NEW] Gate Management Components
GateCard.vue
<div class="gate-card">
  <div class="gate-header">
    <h3>{{ gate.name }}</h3>
    <div class="gate-badges">
      <GateTypeBadge :type="gate.type" />
      <StatusBadge :status="gate.operational_status" />
    </div>
  </div>
  
  <div class="gate-info">
    <!-- Type de gate -->
    <div class="info-row">
      <span class="label">Type:</span>
      <span class="value">{{ gateTypeLabel[gate.type] }}</span>
    </div>
    
    <!-- Horaires -->
    <div class="info-row" v-if="gate.schedule">
      <span class="label">Horaires:</span>
      <span class="value">
        {{ gate.schedule.start_time }} - {{ gate.schedule.end_time }}
      </span>
    </div>
    
    <!-- Ticket types autorisés -->
    <div class="info-row">
      <span class="label">Tickets autorisés:</span>
      <div class="ticket-badges">
        <Badge 
          v-for="ttId in gate.ticket_type_ids"
          :key="ttId"
        >
          {{ getTicketTypeName(ttId) }}
        </Badge>
      </div>
    </div>
    
    <!-- Agent assigné -->
    <div class="info-row">
      <span class="label">Agent:</span>
      <AgentBadge 
        v-if="gate.agent_id"
        :agent="getAgent(gate.agent_id)"
      />
      <span v-else class="text-muted">Non assigné</span>
    </div>
  </div>
  
  <div class="gate-actions">
    <button @click="$emit('toggle-status')">
      {{ gate.operational_status === 'active' ? 'Pause' : 'Activer' }}
    </button>
    <button @click="$emit('edit')">Éditer</button>
    <button @click="$emit('delete')" class="btn-danger">Supprimer</button>
  </div>
</div>
GateFormModal.vue
<Modal v-model="isOpen" title="Configuration de Gate">
  <div class="form-fields">
    <!-- Nom -->
    <input v-model="formData.name" placeholder="Nom de la gate" />
    
    <!-- Type de gate -->
    <div class="gate-type-selector">
      <label>Type de gate</label>
      <div class="type-options">
        <button 
          v-for="type in gateTypes"
          :class="{ active: formData.type === type.value }"
          @click="formData.type = type.value"
        >
          <component :is="type.icon" />
          {{ type.label }}
        </button>
      </div>
    </div>
    
    <!-- Horaires d'activité -->
    <div class="schedule-section">
      <h4>Horaires d'activité</h4>
      <div class="time-inputs">
        <input type="time" v-model="formData.schedule.start_time" />
        <span>à</span>
        <input type="time" v-model="formData.schedule.end_time" />
      </div>
      
      <!-- Jours de la semaine -->
      <div class="days-selector">
        <label v-for="day in weekDays">
          <input 
            type="checkbox" 
            v-model="formData.schedule.days"
            :value="day.value"
          />
          {{ day.label }}
        </label>
      </div>
    </div>
    
    <!-- Types de tickets autorisés -->
    <div class="ticket-types-assignment">
      <h4>Types de tickets autorisés</h4>
      <label v-for="ticketType in ticketTypes">
        <input 
          type="checkbox"
          v-model="formData.ticket_type_ids"
          :value="ticketType.id"
        />
        {{ ticketType.name }}
      </label>
    </div>
    
    <!-- Agent assigné -->
    <div class="agent-assignment">
      <h4>Agent de contrôle</h4>
      <AgentSelector 
        v-model="formData.agent_id"
        :agents="availableAgents"
      >
        <template #no-results>
          <button @click="showCreateAgent = true" class="create-agent-btn">
            + Créer un nouvel agent
          </button>
        </template>
      </AgentSelector>
    </div>
    
    <!-- Statut opérationnel -->
    <div class="status-field">
      <label>Statut initial</label>
      <select v-model="formData.operational_status">
        <option value="active">🟢 Actif</option>
        <option value="inactive">🔴 Inactif</option>
        <option value="paused">🟡 En pause</option>
      </select>
    </div>
  </div>
  
  <template #footer>
    <button @click="$emit('submit', formData)" class="btn-primary">
      Enregistrer
    </button>
  </template>
</Modal>
<!-- Modal de création d'agent -->
<CreateAgentModal
  v-model="showCreateAgent"
  @created="handleAgentCreated"
/>
5. [NEW] Agent Components
AgentSelector.vue
<div class="agent-selector">
  <div class="search-box">
    <input 
      v-model="searchQuery"
      placeholder="Rechercher un agent..."
      @input="filterAgents"
    />
  </div>
  
  <div class="agents-list">
    <div 
      v-for="agent in filteredAgents"
      :key="agent.id"
      class="agent-option"
      :class="{ selected: modelValue === agent.id }"
      @click="$emit('update:modelValue', agent.id)"
    >
      <img :src="agent.photo" class="agent-avatar" />
      <div class="agent-info">
        <span class="agent-name">{{ agent.name }}</span>
        <StatusBadge :status="agent.availability" size="sm" />
      </div>
    </div>
    
    <div v-if="filteredAgents.length === 0" class="no-results">
      <slot name="no-results">
        Aucun agent trouvé
      </slot>
    </div>
  </div>
</div>
CreateAgentModal.vue ⭐
<Modal v-model="isOpen" title="Créer un nouvel agent">
  <div class="form-fields">
    <div class="field">
      <label>Nom complet *</label>
      <input v-model="formData.name" required />
    </div>
    
    <div class="field">
      <label>Email *</label>
      <input v-model="formData.email" type="email" required />
    </div>
    
    <div class="field">
      <label>Téléphone</label>
      <input v-model="formData.phone" type="tel" />
    </div>
    
    <div class="field">
      <label>Photo de profil</label>
      <ImageUploader 
        v-model="formData.photo"
        :max-size="2097152"
        accept="image/*"
      />
    </div>
    
    <div class="field">
      <label>Statut initial</label>
      <select v-model="formData.availability">
        <option value="active">Actif</option>
        <option value="inactive">Inactif</option>
      </select>
    </div>
  </div>
  
  <template #footer>
    <button @click="createAgent" class="btn-primary">
      Créer et assigner
    </button>
  </template>
</Modal>
6. [NEW] Media Components
MediaGallery.vue
<div class="section">
  <h2>🖼️ Médias</h2>
  
  <!-- Banner principal -->
  <div class="banner-upload">
    <h3>Image principale (Banner)</h3>
    <ImageUploader
      v-model="banner"
      :max-size="5242880"
      recommended-size="1200x600"
      @upload="handleBannerUpload"
    />
  </div>
  
  <!-- Galerie secondaire -->
  <div class="gallery-upload">
    <h3>Galerie d'images (max 5)</h3>
    <MultiImageUploader
      v-model="gallery"
      :max-files="5"
      :max-size="5242880"
      @upload="handleGalleryUpload"
    />
  </div>
</div>
Structures de Données
Event Form Data (Updated)
interface EventFormData {
  // Général
  title: string
  description: string
  status: 'draft' | 'published' | 'ongoing' | 'completed' | 'cancelled'
  
  // Dates
  start_datetime: string  // ISO format
  end_datetime: string
  publish_date?: string
  
  // Location (sidebar) - Backend uses single 'location' field
  location: string  // Texte libre pour adresse complète
  capacity: number
  allow_reentry: boolean
  dress_code?: string
  
  // Médias
  banner: File | string
  gallery: (File | string)[]
  
  // Social (sidebar)
  social_links: {
    facebook?: string
    instagram?: string
    twitter?: string
    linkedin?: string
    tiktok?: string
    website?: string
  }
  
  // Tickets
  ticket_types: TicketType[]
  auto_create_gates: boolean
  
  // Gates
  gates: Gate[]
}
Gate Structure (Enhanced)
interface Gate {
  id?: string
  name: string
  type: 'entry' | 'exit' | 'mixed'
  location?: string
  
  // Statut opérationnel
  operational_status: 'active' | 'inactive' | 'paused'
  
  // Horaires
  schedule: {
    start_time: string  // "09:00"
    end_time: string    // "18:00"
    days: ('monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday')[]
  }
  
  // Assignations
  ticket_type_ids: string[]  // Types de tickets autorisés
  agent_id: string | null    // Agent assigné
  
  // Capacité
  max_capacity?: number
  current_count?: number
}
Agent Structure
interface Agent {
  id: string
  name: string
  email: string
  phone?: string
  photo?: string
  availability: 'active' | 'inactive' | 'on_break'
  shift_start?: string
  shift_end?: string
}
Workflow de Création
Utilisateur arrive sur la page

Layout WordPress s'affiche
Titre en focus
Remplissage progressif

Description dans l'éditeur riche
Ajout de types de tickets
Option: cocher "Auto-create gates"
Configuration des Gates

Si auto-create: gates pré-remplies
Sinon: création manuelle
Pour chaque gate:
Définir type et horaires
Assigner types de tickets
Sélectionner/créer agent
Médias

Upload banner (obligatoire)
Ajout galerie (optionnel)
Sidebar - Finalisation

Définir statut
Remplir localisation
Ajouter liens sociaux
Choisir action: Draft/Publish/Schedule
Validation
Champs Obligatoires
✅ Titre
✅ Date de début
✅ Capacité > 0
✅ Au moins un type de ticket
✅ Au moins une gate
✅ Banner image
Validations Logiques
Date fin > Date début
Somme quotas tickets <= Capacité
Chaque gate a au moins 1 ticket type
Horaires gate cohérents (end > start)
Prochaines Étapes
✅ Plan validé et amendé
Créer composants de base (panels sidebar)
Implémenter GatesSection avec statuts
Créer CreateAgentModal
Intégrer auto-create gates
Tester workflow complet
Step1GeneralInfo.vue
Champs: titre, description, start/end datetime
Nouveau: Sélecteur de statut avec badges visuels
Validation: titre requis, dates cohérentes
Step2Location.vue
Champs: venue, address, city, country, capacity
Options: allow_reentry, dress_code
Nouveau: Carte interactive (optionnel)
Step3Media.vue
Upload d'image principale (banner)
Galerie d'images secondaires (max 5)
Drag & drop zone
Prévisualisation avec crop/resize
Format: JPG, PNG, WebP (max 5MB par image)
<ImageUploader
  :max-files="5"
  :max-size="5242880"
  accept="image/*"
  @upload="handleImageUpload"
/>
Step4SocialLinks.vue
Liens vers réseaux sociaux
Validation d'URL
Icônes de réseaux sociaux
socialLinks: {
  facebook: string
  instagram: string
  twitter: string
  linkedin: string
  tiktok: string
  website: string
}
Step5TicketTypes.vue
Liste des types de tickets
Ajout/Suppression/Édition
Champs: name, price, quota, validity dates, usage_limit
Nouveau: Couleur/badge pour identification visuelle
Step6Gates.vue ⭐ NOUVEAU & COMPLEXE
Structure de Gate:

interface Gate {
  id?: string
  name: string
  type: 'entry' | 'exit' | 'mixed'
  location: string
  ticket_type_ids: string[] // Types de tickets autorisés
  agent_id: string | null    // Agent assigné
  max_capacity: number
  is_active: boolean
}
UI Features:

Liste des gates avec cartes visuelles

Formulaire d'ajout/édition de gate

Type de gate:

🟢 Entry (Entrée uniquement)
🔴 Exit (Sortie uniquement)
🟡 Mixed (Entrée/Sortie)
Assignment de types de tickets:

Checkboxes pour sélectionner quels types de tickets peuvent utiliser cette porte
Ex: "VIP Gate" → uniquement tickets VIP
Ex: "General Gate" → tickets Standard + VIP
Assignment d'agent:

Dropdown avec recherche d'agents
Affichage: nom, photo, statut (disponible/occupé)
Un agent peut être assigné à plusieurs gates
<GateCard
  v-for="gate in gates"
  :key="gate.id"
  :gate="gate"
  :ticket-types="ticketTypes"
  :agents="availableAgents"
  @edit="editGate"
  @delete="deleteGate"
/>
Step7Review.vue
Résumé visuel de toutes les informations
Sections collapsibles
Boutons d'action finaux:
Save as Draft
Publish Event
Schedule Publication
3. [NEW] Composants Utilitaires
StepProgress.vue
<div class="step-progress">
  <div v-for="step in steps" class="step-item">
    <div class="step-circle" :class="stepClass(step)">
      {{ step.number }}
    </div>
    <span class="step-label">{{ step.label }}</span>
  </div>
</div>
ImageUploader.vue
Drag & drop zone
Preview avec thumbnails
Crop/resize modal
Progress bar pour upload
Gestion d'erreurs (taille, format)
AgentSelector.vue
Recherche d'agents par nom
Filtrage par disponibilité
Affichage avec avatar et infos
Badge de statut
GateForm.vue
Formulaire complet pour gate
Sélection de type avec icônes
Multi-select pour ticket types
Agent dropdown
Gestion des Gates - Détails
Types de Gates
Type	Description	Icône	Couleur
Entry	Entrée uniquement	🚪➡️	Vert
Exit	Sortie uniquement	➡️🚪	Rouge
Mixed	Entrée/Sortie	🔄	Orange
Relation Gate ↔ Ticket Type
Cas d'usage:

Event avec tickets VIP et Standard
Gate VIP → accepte uniquement tickets VIP
Gate Standard → accepte tickets Standard et VIP
Gate Express → accepte tous les types
Implémentation:

// Dans le formulaire de gate
<div class="ticket-types-assignment">
  <h4>Types de tickets autorisés</h4>
  <div v-for="ticketType in ticketTypes">
    <label>
      <input 
        type="checkbox" 
        v-model="gate.ticket_type_ids"
        :value="ticketType.id"
      />
      {{ ticketType.name }}
    </label>
  </div>
</div>
Assignment d'Agents
Fonctionnalités:

Liste déroulante avec recherche
Affichage des agents disponibles
Indicateur si agent déjà assigné ailleurs
Possibilité d'assigner le même agent à plusieurs gates
<AgentSelector
  v-model="gate.agent_id"
  :agents="agents"
  :show-availability="true"
  placeholder="Sélectionner un agent"
/>
Gestion des Statuts
Workflow de Statut
Draft
Published
Ongoing
Completed
Cancelled
Statuts Disponibles
Statut	Description	Couleur	Actions
draft	Brouillon	Gris	Éditer, Publier, Supprimer
published	Publié	Bleu	Éditer, Annuler, Démarrer
ongoing	En cours	Vert	Terminer, Annuler
completed	Terminé	Violet	Archiver
cancelled	Annulé	Rouge	Réactiver
UI pour Statuts
<StatusSelector
  v-model="formData.status"
  :current-status="event?.status"
  :allow-transitions="allowedTransitions"
/>
Médias & Upload
Images Principales
Banner (obligatoire):

Dimensions recommandées: 1200x600px
Format: JPG, PNG, WebP
Taille max: 5MB
Crop automatique si nécessaire
Galerie d'Images
Images secondaires (optionnel, max 5):

Dimensions: 800x600px
Même formats que banner
Affichage en carousel sur la page publique
Implémentation Upload
async function uploadImages(files: File[]) {
  const formData = new FormData()
  
  files.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })
  
  const response = await api.post('/events/upload-images', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  
  return response.data.urls
}
Réseaux Sociaux
Liens Supportés
interface SocialLinks {
  facebook_event?: string    // URL de l'événement Facebook
  instagram_post?: string    // Lien vers post Instagram
  twitter_post?: string      // Tweet
  linkedin_event?: string    // Événement LinkedIn
  tiktok_video?: string      // Vidéo TikTok
  youtube_video?: string     // Vidéo YouTube
  website?: string           // Site web dédié
}
Validation
Vérifier format URL
Extraire ID si possible (ex: Facebook Event ID)
Prévisualisation du lien (Open Graph)
Données Backend
Payload Complet
interface CreateEventPayload {
  // Général
  title: string
  description: string
  status: EventStatus
  
  // Dates
  start_datetime: string
  end_datetime: string
  timezone: string
  
  // Localisation
  venue: string
  address: string
  city: string
  country: string
  capacity: number
  
  // Options
  allow_reentry: boolean
  dress_code?: string
  
  // Médias
  banner_image: File
  gallery_images?: File[]
  
  // Réseaux sociaux
  social_links?: SocialLinks
  
  // Types de tickets
  ticket_types?: TicketType[]
  
  // Gates
  gates?: Gate[]
}
Expérience Utilisateur
Principes de Design
Progression Claire - Indicateur visuel de l'étape actuelle
Validation Immédiate - Feedback en temps réel
Sauvegarde Auto - Draft sauvegardé automatiquement
Navigation Flexible - Retour en arrière possible
Prévisualisation - Voir le résultat avant publication
Animations & Transitions
.step-transition-enter-active,
.step-transition-leave-active {
  transition: all 0.3s ease;
}
.step-transition-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.step-transition-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
Responsive Design
Mobile: Steps en accordéon
Tablet: 2 colonnes pour formulaires
Desktop: Layout complet avec sidebar de navigation
Validation & Erreurs
Validation par Étape
Étape 1:

✅ Titre (min 5 caractères)
✅ Date de début (future)
✅ Date de fin > date de début
Étape 2:

✅ Capacité > 0
Étape 5:

✅ Au moins un type de ticket
✅ Prix >= 0
✅ Quota <= capacité totale
Étape 6:

✅ Au moins une gate
✅ Chaque gate a un type
✅ Au moins un ticket type assigné par gate
Messages d'Erreur
<Alert v-if="errors.length" type="error">
  <ul>
    <li v-for="error in errors">{{ error }}</li>
  </ul>
</Alert>
Migration depuis Modal
Changements dans EventsListView
Avant:

<EventFormModal v-model="showModal" @submit="handleSubmit" />
Après:

<RouterLink :to="{ name: 'event-create' }">
  <button>Create Event</button>
</RouterLink>
Routes
{
  path: '/dashboard/events/create',
  name: 'event-create',
  component: EventFormView
},
{
  path: '/dashboard/events/:id/edit',
  name: 'event-edit',
  component: EventFormView
}
Fichiers à Modifier/Créer
Modifications
✅ 
EventFormView.vue
✅ 
EventsListView.vue
Nouveaux Composants
✅ components/events/steps/Step1GeneralInfo.vue
✅ components/events/steps/Step2Location.vue
✅ components/events/steps/Step3Media.vue
✅ components/events/steps/Step4SocialLinks.vue
✅ components/events/steps/Step5TicketTypes.vue
✅ components/events/steps/Step6Gates.vue
✅ components/events/steps/Step7Review.vue
✅ components/common/StepProgress.vue
✅ components/common/ImageUploader.vue
✅ components/gates/GateForm.vue
✅ components/gates/GateCard.vue
✅ components/agents/AgentSelector.vue
Prochaines Étapes
Valider le plan avec l'utilisateur
Créer les composants de base (StepProgress, ImageUploader)
Implémenter les étapes une par une
Intégrer la gestion des gates
Tester le workflow complet
Polir l'UX et les animations
IMPORTANT

Ce plan nécessite également des modifications backend pour supporter:

Upload multiple d'images
Gestion des social_links
Relations gate ↔ ticket_type
Relations gate ↔ agent

