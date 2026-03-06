import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { useState } from "react";
import { ScrollView, Switch, Text, TouchableOpacity, View } from "react-native";

export default function Privacidad() {
  const router = useRouter();

  const [cuentaPrivada, setCuentaPrivada] = useState(true);
  const [estadoLinea, setEstadoLinea] = useState(true);
  const [verPerfil, setVerPerfil] = useState(true);
  const [mensajes, setMensajes] = useState(true);

  return (
    <ScrollView className="flex-1 bg-[#F3F4F6] px-5 pt-12">

      {/* HEADER */}
      <View className="flex-row items-center mt-2 mb-8">
        <TouchableOpacity onPress={() => router.back()} className="mr-3">
          <Ionicons name="arrow-back" size={26} color="black" />
        </TouchableOpacity>
        <Text className="text-xl font-bold flex-1 text-center">Privacidad</Text>
      </View>

      {/* SECCIÓN PRIVACIDAD */}
      <View className="bg-white rounded-2xl p-4 shadow mb-4">

        <PrivacyItem
          title="Cuenta privada"
          subtitle="Solo tus contactos pueden ver tu perfil"
          value={cuentaPrivada}
          onChange={setCuentaPrivada}
        />

        <PrivacyItem
          title="Estado en línea"
          subtitle="Mostrar cuando estás conectado"
          value={estadoLinea}
          onChange={setEstadoLinea}
          isLast
        />

      </View>

    </ScrollView>
  );
}

/* COMPONENTE REUTILIZABLE */
function PrivacyItem({ title, subtitle, value, onChange, isLast }: any) {
  return (
    <View
      className={`flex-row items-center justify-between py-3 ${
        isLast ? "" : "border-b border-gray-200"
      }`}
    >
      <View className="pr-4 flex-1">
        <Text className="text-base font-semibold">{title}</Text>
        <Text className="text-gray-500 text-sm">{subtitle}</Text>
      </View>

      <Switch
        value={value}
        onValueChange={onChange}
        trackColor={{ false: "#D1D5DB", true: "#2DC75C" }}
        thumbColor="#ffffff"
      />
    </View>
  );
}