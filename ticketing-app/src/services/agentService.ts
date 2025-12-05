import api from './api'

export interface Agent {
    id: string
    name: string
    email: string
    phone?: string
    photo?: string
    availability: 'active' | 'inactive' | 'on_break'
}

export interface CreateAgentData {
    name: string
    email: string
    phone?: string
    photo?: File
    availability?: 'active' | 'inactive' | 'on_break'
}

class AgentService {
    async getAll(): Promise<Agent[]> {
        const response = await api.get<{ data: Agent[] }>('/agents')
        return response.data.data
    }

    async getById(id: string): Promise<Agent> {
        const response = await api.get<{ data: Agent }>(`/agents/${id}`)
        return response.data.data
    }

    async create(data: CreateAgentData): Promise<Agent> {
        const formData = new FormData()
        formData.append('name', data.name)
        formData.append('email', data.email)
        if (data.phone) formData.append('phone', data.phone)
        if (data.photo) formData.append('photo', data.photo)
        if (data.availability) formData.append('availability', data.availability)

        const response = await api.post<Agent>('/agents', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        return response.data
    }

    async updateStatus(id: string, availability: 'active' | 'inactive' | 'on_break'): Promise<Agent> {
        const response = await api.patch<Agent>(`/agents/${id}/status`, { availability })
        return response.data
    }
}

export default new AgentService()
