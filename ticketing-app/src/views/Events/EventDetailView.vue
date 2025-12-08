<template>
  <DashboardLayout>
    <div v-if="loading" class="space-y-6">
      <div class="animate-pulse">
        <div class="h-64 bg-gray-200 rounded-xl mb-6"></div>
        <div class="h-96 bg-gray-200 rounded-xl"></div>
      </div>
    </div>

    <div v-else-if="event" class="space-y-6">
      <!-- Event Header with Enhanced Banner -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Banner with Gradient Overlay -->
        <div class="h-80 bg-gradient-to-br from-blue-500 to-purple-500 relative">
          <img
            v-if="event.image_url"
            :src="getImageUrl(event.image_url)"
            :alt="event.title"
            class="w-full h-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
          
          <!-- Action Buttons -->
          <div class="absolute top-4 left-4 right-4 flex justify-between items-center z-10">
            <!-- Back Button -->
            <button
              @click="$router.back()"
              class="px-4 py-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-lg hover:bg-white flex items-center gap-2 shadow-lg transition-all hover:scale-105"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              Retour
            </button>

            <!-- Right Action Buttons -->
            <div class="flex gap-2">
              <!-- View Public Page Button -->
              <a
                v-if="event.slug"
                :href="`/events/${event.slug}`"
                target="_blank"
                class="px-4 py-2 bg-white/90 backdrop-blur-sm text-blue-600 rounded-lg hover:bg-white flex items-center gap-2 shadow-lg transition-all hover:scale-105"
              >
                <ExternalLinkIcon class="w-4 h-4" />
                Page publique
              </a>

              <ShareEventButton
                v-if="event.slug"
                :event-slug="event.slug"
                :event-title="event.title"
                :event-description="event.description"
              />
              <button
                v-if="can('update_events')"
                @click="editEvent"
                class="px-4 py-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-lg hover:bg-white flex items-center gap-2 shadow-lg transition-all hover:scale-105"
              >
                <EditIcon class="w-4 h-4" />
                Modifier
              </button>
              <button
                v-if="can('delete_events')"
                @click="confirmDelete"
                class="px-4 py-2 bg-white/90 backdrop-blur-sm text-red-600 rounded-lg hover:bg-white flex items-center gap-2 shadow-lg transition-all hover:scale-105"
              >
                <TrashIcon class="w-4 h-4" />
                Supprimer
              </button>
            </div>
          </div>

          <!-- Event Title Overlay -->
          <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
            <div class="flex items-center gap-3 mb-3">
              <h1 class="text-4xl font-bold drop-shadow-lg">{{ event.title }}</h1>
              <StatusBadge :status="event.status" type="event" />
            </div>
            <p v-if="event.description" class="text-lg text-white/90 drop-shadow mb-3 line-clamp-2">
              {{ event.description }}
            </p>
            <div v-if="event.organisateur" class="flex items-center gap-2 text-white/80">
              <BuildingIcon class="w-5 h-5" />
              <span class="text-lg">{{ event.organisateur.name }}</span>
            </div>
          </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50">
          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-blue-100 rounded-xl">
                <CalendarIcon class="w-6 h-6 text-blue-600" />
              </div>
              <div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Date de début</div>
                <div class="text-sm font-bold text-gray-900">{{ formatDate(event.start_datetime) }}</div>
                <div class="text-xs text-gray-600">{{ formatTime(event.start_datetime) }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-green-100 rounded-xl">
                <TicketIcon class="w-6 h-6 text-green-600" />
              </div>
              <div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Billets vendus</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ event.tickets_sold || 0 }} / {{ event.capacity }}
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                  <div
                    class="bg-green-600 h-1.5 rounded-full transition-all"
                    :style="{ width: `${((event.tickets_sold || 0) / event.capacity) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-purple-100 rounded-xl">
                <DollarSignIcon class="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Revenus</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ formatCurrency(event.revenue || 0) }}
                </div>
                <div class="text-xs text-green-600">+12% ce mois</div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-orange-100 rounded-xl">
                <UsersIcon class="w-6 h-6 text-orange-600" />
              </div>
              <div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Présents</div>
                <div class="text-sm font-bold text-gray-900">
                  {{ event.current_in || 0 }}
                </div>
                <div class="text-xs text-gray-600">participants actuels</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tabs -->
          <Tabs v-model="activeTab" :tabs="tabs">
            <!-- Overview Tab -->
            <template #overview>
              <div class="space-y-6">
                <!-- Event Details Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                  <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                    <InfoIcon class="w-5 h-5 text-blue-600" />
                    Détails de l'événement
                  </h2>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date & Time -->
                    <div>
                      <h3 class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">
                        <CalendarIcon class="w-4 h-4" />
                        Date & Heure
                      </h3>
                      <div class="space-y-2 bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                          <span class="text-sm text-gray-600">Début</span>
                          <span class="text-sm font-medium text-gray-900">
                            {{ formatDate(event.start_datetime) }} à {{ formatTime(event.start_datetime) }}
                          </span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-sm text-gray-600">Fin</span>
                          <span class="text-sm font-medium text-gray-900">
                            {{ formatDate(event.end_datetime) }} à {{ formatTime(event.end_datetime) }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Location -->
                    <div>
                      <h3 class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">
                        <MapPinIcon class="w-4 h-4" />
                        Localisation
                      </h3>
                      <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm font-medium text-gray-900">{{ event.location }}</div>
                        <button class="mt-2 text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1">
                          <MapIcon class="w-3 h-3" />
                          Voir sur la carte
                        </button>
                      </div>
                    </div>

                    <!-- Capacity -->
                    <div>
                      <h3 class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">
                        <UsersIcon class="w-4 h-4" />
                        Capacité
                      </h3>
                      <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ event.capacity }}</div>
                        <div class="text-xs text-gray-600 mt-1">personnes maximum</div>
                        <div class="mt-3 bg-gray-200 rounded-full h-2">
                          <div
                            class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all"
                            :style="{ width: `${((event.tickets_sold || 0) / event.capacity) * 100}%` }"
                          ></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                          {{ ((event.tickets_sold || 0) / event.capacity * 100).toFixed(1) }}% rempli
                        </div>
                      </div>
                    </div>

                    <!-- Additional Info -->
                    <div>
                      <h3 class="text-sm font-medium text-gray-500 mb-3">Informations supplémentaires</h3>
                      <div class="space-y-2 bg-gray-50 rounded-lg p-4">
                        <div v-if="event.dress_code" class="flex items-center gap-2 text-sm">
                          <ShirtIcon class="w-4 h-4 text-gray-400" />
                          <span class="text-gray-600">Dress Code:</span>
                          <span class="font-medium text-gray-900">{{ event.dress_code }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                          <RepeatIcon class="w-4 h-4 text-gray-400" />
                          <span class="text-gray-600">Re-entrée:</span>
                          <span :class="event.allow_reentry ? 'text-green-600' : 'text-red-600'" class="font-medium">
                            {{ event.allow_reentry ? 'Autorisée' : 'Non autorisée' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Gallery (if available) -->
                <div v-if="event.gallery_images && event.gallery_images.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                  <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <ImageIcon class="w-5 h-5 text-blue-600" />
                    Galerie photos
                  </h2>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                      v-for="(image, index) in event.gallery_images"
                      :key="index"
                      class="aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                    >
                      <img
                        :src="getImageUrl(image)"
                        :alt="`Gallery image ${index + 1}`"
                        class="w-full h-full object-cover"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Ticket Types Tab -->
            <template #ticket-types>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Types de billets</h2>
                  <button
                    v-if="can('manage_ticket_types')"
                    @click="showTicketTypeModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
                  >
                    <PlusIcon class="w-5 h-5" />
                    Ajouter un type
                  </button>
                </div>

                <div v-if="ticketTypes.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    v-for="ticketType in ticketTypes"
                    :key="ticketType.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
                  >
                    <div class="flex items-start justify-between mb-4">
                      <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ ticketType.name }}</h3>
                        <p v-if="ticketType.description" class="text-sm text-gray-600 mt-1">
                          {{ ticketType.description }}
                        </p>
                      </div>
                      <StatusBadge :status="ticketType.is_active ? 'active' : 'inactive'" type="ticket" />
                    </div>

                    <div class="space-y-3">
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Prix</span>
                        <span class="text-xl font-bold text-gray-900">
                          {{ formatCurrency(ticketType.price) }}
                        </span>
                      </div>

                      <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Disponibles</span>
                        <span class="text-sm font-medium text-gray-900">
                          {{ ticketType.quantity_available }} / {{ ticketType.quota }}
                        </span>
                      </div>

                      <div class="bg-gray-200 rounded-full h-2">
                        <div
                          class="bg-green-600 h-2 rounded-full transition-all"
                          :style="{ width: `${(ticketType.quantity_available / ticketType.quota * 100)}%` }"
                        ></div>
                      </div>

                      <div v-if="ticketType.sale_start_date" class="text-xs text-gray-500 pt-2 border-t">
                        Vente: {{ formatDate(ticketType.sale_start_date) }} - {{ formatDate(ticketType.sale_end_date) }}
                      </div>
                    </div>

                    <div v-if="can('manage_ticket_types')" class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                      <button
                        @click="printTicketType(ticketType)"
                        class="flex-1 px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors flex items-center justify-center gap-2"
                      >
                        <PrinterIcon class="w-4 h-4" />
                        Imprimer
                      </button>
                      <button
                        @click="editTicketType(ticketType)"
                        class="flex-1 px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                      >
                        Modifier
                      </button>
                      <button
                        @click="confirmDeleteTicketType(ticketType)"
                        class="flex-1 px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                      >
                        Supprimer
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                  <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun type de billet</h3>
                  <p class="text-gray-600 mb-6">Créez des types de billets pour commencer à vendre</p>
                  <button
                    v-if="can('manage_ticket_types')"
                    @click="showTicketTypeModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
                  >
                    <PlusIcon class="w-5 h-5" />
                    Ajouter un type de billet
                  </button>
                </div>
              </div>
            </template>

            <!-- Gates Tab -->
            <template #gates>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-semibold text-gray-900">Portes d'accès</h2>
                  <button
                    v-if="can('manage_gates')"
                    @click="showGateModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                  >
                    <PlusIcon class="w-5 h-5" />
                    Ajouter une porte
                  </button>
                </div>

                <div v-if="event.gates && event.gates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <GateCard
                    v-for="gate in event.gates"
                    :key="gate.id"
                    :gate="gate"
                    @edit="editGate"
                    @delete="confirmDeleteGate"
                    @assignScanner="assignAgentToGate"
                  />
                </div>

                <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                  <DoorOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune porte configurée</h3>
                  <p class="text-gray-600 mb-6">Ajoutez des portes pour gérer les points d'entrée et de sortie</p>
                  <button
                    v-if="can('manage_gates')"
                    @click="showGateModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
                  >
                    <PlusIcon class="w-5 h-5" />
                    Ajouter une porte
                  </button>
                </div>
              </div>
            </template>

            <!-- Statistics Tab -->
            <template #statistics>
              <div class="space-y-6">
                <EventStats :event-id="event.id" />
              </div>
            </template>
          </Tabs>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
          <!-- Social Links Card -->
          <div v-if="event.social_links && hasSocialLinks" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <Share2Icon class="w-5 h-5 text-blue-600" />
              Réseaux sociaux
            </h3>
            <div class="space-y-3">
              <a
                v-if="event.social_links.facebook"
                :href="event.social_links.facebook"
                target="_blank"
                class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group"
              >
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                  <FacebookIcon class="w-5 h-5 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-900 group-hover:text-blue-600">Facebook</span>
                <ExternalLinkIcon class="w-4 h-4 text-gray-400 ml-auto" />
              </a>

              <a
                v-if="event.social_links.instagram"
                :href="event.social_links.instagram"
                target="_blank"
                class="flex items-center gap-3 p-3 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors group"
              >
                <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                  <InstagramIcon class="w-5 h-5 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-900 group-hover:text-pink-600">Instagram</span>
                <ExternalLinkIcon class="w-4 h-4 text-gray-400 ml-auto" />
              </a>

              <a
                v-if="event.social_links.twitter"
                :href="event.social_links.twitter"
                target="_blank"
                class="flex items-center gap-3 p-3 bg-sky-50 rounded-lg hover:bg-sky-100 transition-colors group"
              >
                <div class="w-10 h-10 bg-sky-500 rounded-lg flex items-center justify-center">
                  <TwitterIcon class="w-5 h-5 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-900 group-hover:text-sky-600">Twitter / X</span>
                <ExternalLinkIcon class="w-4 h-4 text-gray-400 ml-auto" />
              </a>

              <a
                v-if="event.social_links.website"
                :href="event.social_links.website"
                target="_blank"
                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group"
              >
                <div class="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center">
                  <GlobeIcon class="w-5 h-5 text-white" />
                </div>
                <span class="text-sm font-medium text-gray-900 group-hover:text-gray-600">Site web</span>
                <ExternalLinkIcon class="w-4 h-4 text-gray-400 ml-auto" />
              </a>
            </div>
          </div>

          <!-- Quick Actions Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
            <div class="space-y-2">
              <button
                v-if="event.status === 'draft'"
                @click="handlePublish"
                class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 transition-colors"
              >
                <RocketIcon class="w-5 h-5" />
                Publier l'événement
              </button>
              <button
                class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 transition-colors"
              >
                <DownloadIcon class="w-5 h-5" />
                Exporter les données
              </button>
              <button
                class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center justify-center gap-2 transition-colors"
              >
                <PrinterIcon class="w-5 h-5" />
                Imprimer le rapport
              </button>
            </div>
          </div>

          <!-- Event Timeline -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <ClockIcon class="w-5 h-5 text-blue-600" />
              Timeline
            </h3>
            <div class="space-y-4">
              <div class="flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <CheckIcon class="w-4 h-4 text-green-600" />
                  </div>
                  <div class="w-0.5 h-full bg-gray-200 mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                  <div class="text-sm font-medium text-gray-900">Événement créé</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDate(event.created_at) }}</div>
                </div>
              </div>

              <div v-if="event.status === 'published'" class="flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <RocketIcon class="w-4 h-4 text-blue-600" />
                  </div>
                  <div class="w-0.5 h-full bg-gray-200 mt-2"></div>
                </div>
                <div class="flex-1 pb-4">
                  <div class="text-sm font-medium text-gray-900">Publié</div>
                  <div class="text-xs text-gray-500 mt-1">Visible par le public</div>
                </div>
              </div>

              <div class="flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <CalendarIcon class="w-4 h-4 text-gray-400" />
                  </div>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900">Début de l'événement</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDate(event.start_datetime) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
      <AlertCircleIcon class="w-16 h-16 text-red-400 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Événement introuvable</h3>
      <p class="text-gray-600 mb-6">L'événement que vous recherchez n'existe pas</p>
      <RouterLink
        :to="{ name: 'events' }"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Retour aux événements
      </RouterLink>
    </div>

    <!-- Modals -->
    <TicketTypeFormModal
      v-model="showTicketTypeModal"
      :event-id="event?.id"
      :ticket-type="selectedTicketType"
      @submit="handleTicketTypeSubmit"
    />

    <GateFormModal
      v-model="showGateModal"
      :event-id="event?.id"
      :gate="selectedGate"
      @submit="handleGateSubmit"
    />

    <AssignAgentModal
      v-model="showAssignAgentModal"
      :gate="gateToAssign"
      @submit="handleAssignAgent"
    />

    <ConfirmModal
      v-model="showDeleteModal"
      title="Supprimer l'événement"
      message="Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible."
      variant="danger"
      confirm-text="Supprimer"
      @confirm="handleDelete"
    />


    <ConfirmModal
      v-model="showDeleteTicketTypeModal"
      title="Supprimer le type de billet"
      message="Êtes-vous sûr de vouloir supprimer ce type de billet ? Cette action est irréversible."
      variant="danger"
      confirm-text="Supprimer"
      @confirm="handleDeleteTicketType"
    />

    <ConfirmModal
      v-model="showDeleteGateModal"
      title="Supprimer la porte"
      message="Êtes-vous sûr de vouloir supprimer cette porte ? Cette action est irréversible."
      variant="danger"
      confirm-text="Supprimer"
      @confirm="handleDeleteGate"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useEvents } from '@/composables/useEvents'
import { useTicketTypes } from '@/composables/useTicketTypes'
import { useGates } from '@/composables/useGates'
import { usePermissions } from '@/composables/usePermissions'
import { useNotificationStore } from '@/stores/notifications'
import eventService from '@/services/eventService'
import gateService from '@/services/gateService'
import { formatDate, formatCurrency, formatTime, getImageUrl } from '@/utils/formatters'
import type { TicketType, Gate } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Tabs from '@/components/common/Tabs.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ShareEventButton from '@/components/events/ShareEventButton.vue'
import EventStats from '@/components/events/EventStats.vue'
import TicketTypeFormModal from '@/components/tickets/TicketTypeFormModal.vue'
import GateCard from '@/components/gates/GateCard.vue'
import GateFormModal from '@/components/gates/GateFormModal.vue'
import AssignAgentModal from '@/components/gates/AssignAgentModal.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  CalendarIcon,
  ClockIcon,
  MapPinIcon,
  TicketIcon,
  DollarSignIcon,
  UsersIcon,
  BuildingIcon,
  EditIcon,
  TrashIcon,
  PlusIcon,
  AlertCircleIcon,
  ArrowLeftIcon,
  DoorOpenIcon,
  ShirtIcon,
  RepeatIcon,
  InfoIcon,
  ImageIcon,
  Share2Icon,
  FacebookIcon,
  InstagramIcon,
  TwitterIcon,
  GlobeIcon,
  ExternalLinkIcon,
  RocketIcon,
  DownloadIcon,
  PrinterIcon,
  CheckIcon,
  MapIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { event, fetchEvent, updateEvent, deleteEvent } = useEvents()
const { ticketTypes, fetchTicketTypes, createTicketType, updateTicketType, deleteTicketType } = useTicketTypes()
const { gates, fetchGates, createGate, updateGate, deleteGate } = useGates()
const { can } = usePermissions()
const notifications = useNotificationStore()

const loading = ref(true)
const activeTab = ref('overview')

const showTicketTypeModal = ref(false)
const showGateModal = ref(false)
const showAssignAgentModal = ref(false)
const showDeleteModal = ref(false)
const showDeleteTicketTypeModal = ref(false)
const showDeleteGateModal = ref(false)

const selectedTicketType = ref<TicketType | null>(null)
const selectedGate = ref<Gate | null>(null)
const gateToAssign = ref<Gate | null>(null)
const ticketTypeToDelete = ref<TicketType | null>(null)
const gateToDelete = ref<Gate | null>(null)

const tabs = computed(() => [
  { key: 'overview', label: 'Vue d\'ensemble', icon: InfoIcon },
  { key: 'ticket-types', label: 'Types de billets', icon: TicketIcon },
  { key: 'gates', label: 'Portes', icon: DoorOpenIcon },
  { key: 'statistics', label: 'Statistiques', icon: UsersIcon }
])

const hasSocialLinks = computed(() => {
  if (!event.value?.social_links) return false
  const links = event.value.social_links
  return !!(links.facebook || links.instagram || links.twitter || links.website)
})

onMounted(async () => {
  await loadEvent()
})

async function loadEvent() {
  loading.value = true
  try {
    const eventId = route.params.id as string
    await fetchEvent(eventId)

    if (event.value) {
      await Promise.all([
        fetchTicketTypes(event.value.id),
        fetchGates(event.value.id)
      ])
    }
  } catch (error) {
    console.error('Failed to load event:', error)
  } finally {
    loading.value = false
  }
}

function editEvent() {
  router.push({ name: 'event-edit', params: { id: event.value?.id } })
}

async function handlePublish() {
  if (!event.value) return
  
  try {
    await eventService.publish(event.value.id)
    notifications.success('Succès', 'Événement publié avec succès !')
    await loadEvent()
  } catch (error: any) {
    const message = error.response?.data?.message || 'Impossible de publier l\'événement'
    notifications.error('Erreur', message)
    console.error('Error publishing event:', error)
  }
}

function confirmDelete() {
  showDeleteModal.value = true
}

async function handleDelete() {
  if (event.value) {
    await deleteEvent(event.value.id)
    router.push('/dashboard/events')
  }
}

// Ticket Type handlers
function editTicketType(ticketType: TicketType) {
  selectedTicketType.value = ticketType
  showTicketTypeModal.value = true
}

function confirmDeleteTicketType(ticketType: TicketType) {
  ticketTypeToDelete.value = ticketType
  showDeleteTicketTypeModal.value = true
}

async function handleTicketTypeSubmit(data: any) {
  if (selectedTicketType.value) {
    await updateTicketType(selectedTicketType.value.id, data)
  } else {
    await createTicketType({ ...data, event_id: event.value?.id })
  }
  selectedTicketType.value = null
  if (event.value?.id) {
    await fetchTicketTypes(event.value.id)
  }
}

async function handleDeleteTicketType() {
  if (ticketTypeToDelete.value) {
    await deleteTicketType(ticketTypeToDelete.value.id)
    ticketTypeToDelete.value = null
    if (event.value?.id) {
      await fetchTicketTypes(event.value.id)
    }
  }
}

// Gate handlers
function editGate(gate: Gate) {
  selectedGate.value = gate
  showGateModal.value = true
}

function confirmDeleteGate(gateId: string) {
  const gate = event.value?.gates?.find(g => g.id === gateId)
  if (gate) {
    gateToDelete.value = gate
    showDeleteGateModal.value = true
  }
}

async function handleGateSubmit(data: any) {
  if (selectedGate.value) {
    await updateGate(selectedGate.value.id, data)
  } else {
    await createGate({ ...data, event_id: event.value?.id })
  }
  selectedGate.value = null
  if (event.value?.id) {
    await fetchGates(event.value.id)
  }
}

async function handleDeleteGate() {
  if (gateToDelete.value) {
    await deleteGate(gateToDelete.value.id)
    gateToDelete.value = null
    if (event.value?.id) {
      await fetchGates(event.value.id)
    }
  }
}

function assignAgentToGate(gate: Gate) {
  gateToAssign.value = gate
  showAssignAgentModal.value = true
}

async function handleAssignAgent(agentId: string) {
  if (!gateToAssign.value || !event.value) return

  try {
    await gateService.assignAgent(event.value.id, gateToAssign.value.id, agentId)
    notifications.success('Succès', 'Agent assigné avec succès')
    // Refresh gates to show updated agent
    await fetchGates(event.value.id)
  } catch (error: any) {
    const message = error.response?.data?.message || 'Impossible d\'assigner l\'agent'
    notifications.error('Erreur', message)
  }
}


// Print ticket type
function printTicketType(ticketType: TicketType) {
  const printWindow = window.open('', '_blank')
  if (!printWindow) return

  const printContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Type de Ticket - ${ticketType.name}</title>
      <style>
        @media print {
          @page { margin: 2cm; }
        }
        body {
          font-family: Arial, sans-serif;
          padding: 20px;
          max-width: 800px;
          margin: 0 auto;
        }
        .header {
          text-align: center;
          margin-bottom: 30px;
          border-bottom: 3px solid #2563eb;
          padding-bottom: 20px;
        }
        .header h1 {
          color: #1e40af;
          margin: 0 0 10px 0;
        }
        .event-name {
          color: #6b7280;
          font-size: 18px;
        }
        .details {
          margin: 30px 0;
        }
        .detail-row {
          display: flex;
          justify-content: space-between;
          padding: 15px;
          border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:nth-child(even) {
          background-color: #f9fafb;
        }
        .detail-label {
          font-weight: bold;
          color: #374151;
        }
        .detail-value {
          color: #1f2937;
        }
        .footer {
          margin-top: 40px;
          text-align: center;
          color: #6b7280;
          font-size: 12px;
          border-top: 1px solid #e5e7eb;
          padding-top: 20px;
        }
        .print-button {
          display: block;
          margin: 20px auto;
          padding: 10px 30px;
          background-color: #2563eb;
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-size: 16px;
        }
        .print-button:hover {
          background-color: #1d4ed8;
        }
        @media print {
          .print-button { display: none; }
        }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>Type de Ticket</h1>
        <div class="event-name">${event.value?.title || 'Événement'}</div>
      </div>

      <div class="details">
        <div class="detail-row">
          <span class="detail-label">Nom du type :</span>
          <span class="detail-value">${ticketType.name}</span>
        </div>
        ${ticketType.description ? `
        <div class="detail-row">
          <span class="detail-label">Description :</span>
          <span class="detail-value">${ticketType.description}</span>
        </div>
        ` : ''}
        <div class="detail-row">
          <span class="detail-label">Prix :</span>
          <span class="detail-value">${formatCurrency(ticketType.price)}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Quota total :</span>
          <span class="detail-value">${ticketType.quota || 'Illimité'}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Disponibles :</span>
          <span class="detail-value">${ticketType.quantity_available} / ${ticketType.quota}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Vendus :</span>
          <span class="detail-value">${ticketType.quantity_sold || 0}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Limite d'utilisation :</span>
          <span class="detail-value">${ticketType.usage_limit || 1} fois</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Statut :</span>
          <span class="detail-value">${ticketType.is_active ? 'Actif' : 'Inactif'}</span>
        </div>
      </div>

      <div class="footer">
        <p>Document généré le ${new Date().toLocaleDateString('fr-FR', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })}</p>
        <p>${event.value?.organisateur?.name || 'Organisation'}</p>
      </div>

      <button class="print-button" onclick="window.print()">Imprimer</button>
    </body>
    </html>
  `

  printWindow.document.write(printContent)
  printWindow.document.close()
}


// Gate handlers
function editGate(gate: Gate) {
  selectedGate.value = gate
  showGateModal.value = true
}

function confirmDeleteGate(gate: Gate) {
  gateToDelete.value = gate
  showDeleteGateModal.value = true
}

async function handleGateSubmit(data: any) {
  if (selectedGate.value) {
    await updateGate(selectedGate.value.id, data)
  } else {
    await createGate({ ...data, event_id: event.value?.id })
  }
  selectedGate.value = null
  if (event.value?.id) {
    await fetchGates(event.value.id)
  }
}

async function handleDeleteGate() {
  if (gateToDelete.value) {
    await deleteGate(gateToDelete.value.id)
    gateToDelete.value = null
    if (event.value?.id) {
      await fetchGates(event.value.id)
    }
  }
}

function assignAgentToGate(gate: Gate) {
  gateToAssign.value = gate
  showAssignAgentModal.value = true
}

async function handleAssignAgent(agentId: string) {
  if (!gateToAssign.value || !event.value) return

  try {
    await useGates().assignAgent(event.value.id, gateToAssign.value.id, agentId)
    notifications.success('Succès', 'Agent assigné avec succès')
    showAssignAgentModal.value = false
    gateToAssign.value = null
    await fetchGates(event.value.id)
  } catch (error: any) {
    const message = error.response?.data?.message || "Impossible d'assigner l'agent"
    notifications.error('Erreur', message)
  }
}

</script>
