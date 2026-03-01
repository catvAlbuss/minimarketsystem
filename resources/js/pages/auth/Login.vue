<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Iniciar Sesión" />

    <!-- Fondo -->
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        
        <!-- Card de Login -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
            
            <!-- Logo y Título -->
            <div class="text-center mb-6">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow-lg">
                        <svg class="h-7 w-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">NetMarket</h1>
            </div>

            <!-- Mensaje de estado -->
            <div v-if="status" class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm text-center border border-green-200">
                {{ status }}
            </div>

            <!-- Formulario -->
            <Form v-bind="store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }"
                class="flex flex-col gap-4">
                
                <div class="grid gap-4">
                    
                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-sm font-medium text-gray-700">Correo electrónico</Label>
                        <Input 
                            id="email" 
                            type="email" 
                            name="email" 
                            required 
                            autofocus 
                            :tabindex="1" 
                            autocomplete="email"
                            placeholder="ejemplo@correo.com"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20']"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-sm font-medium text-gray-700">Contraseña</Label>
                            <TextLink v-if="canResetPassword" :href="request()" class="text-sm text-blue-600 hover:text-blue-700 transition-colors" :tabindex="5">
                                ¿Olvidaste tu contraseña?
                            </TextLink>
                        </div>
                        <Input 
                            id="password" 
                            type="password" 
                            name="password" 
                            required 
                            :tabindex="2"
                            autocomplete="current-password" 
                            placeholder="••••••••"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20']"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center gap-2 cursor-pointer">
                            <Checkbox 
                                id="remember" 
                                name="remember" 
                                :tabindex="3"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                            />
                            <span class="text-sm text-gray-600">Recordarme</span>
                        </Label>
                    </div>

                    <!-- Botón Login -->
                    <Button 
                        type="submit" 
                        class="mt-2 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-[1.02] hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-600/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        :tabindex="4" 
                        :disabled="processing" 
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                        {{ processing ? 'Iniciando...' : 'Iniciar Sesión' }}
                    </Button>
                </div>

                <!-- Link para registrarse -->
                <div class="text-center text-sm text-gray-500 mt-4" v-if="canRegister">
                    ¿No tienes una cuenta?
                    <TextLink :href="register()" :tabindex="5" class="font-medium text-blue-600 hover:text-blue-700 transition-colors ml-1">
                        Regístrate gratis
                    </TextLink>
                </div>
            </Form>


        </div>
    </div>
</template>

