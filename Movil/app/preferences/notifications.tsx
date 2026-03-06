import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { useState } from "react";
import { ScrollView, Switch, Text, TouchableOpacity, View } from "react-native";

export default function Notificaciones() {
  const router = useRouter();

  const [push, setPush] = useState(true);
  const [email, setEmail] = useState(false);
  const [sonido, setSonido] = useState(true);
  const [vibracion, setVibracion] = useState(true);

  return (
    <ScrollView className="flex-1 bg-[#F3F4F6] px-5 pt-12">

      {/* HEADER */}
      <View className="flex-row items-center mb-8">
        <TouchableOpacity onPress={() => router.back()} className="mr-3">
          <Ionicons name="arrow-back" size={26} color="black" />
        </TouchableOpacity>
        <Text className="text-xl font-bold flex-1 text-center">Notificaciones</Text>
      </View>

      {/* SECCIÓN */}
      <View className="bg-white rounded-2xl p-4 shadow mb-4">

        <NotificationItem
          title="Notificaciones por correo"
          subtitle="Recibir correos electrónicos"
          value={email}
          onChange={setEmail}
        />

        <NotificationItem
          title="Sonido"
          subtitle="Reproducir sonido al recibir alertas"
          value={sonido}
          onChange={setSonido}
        />

        <NotificationItem
          title="Vibración"
          subtitle="Vibrar al recibir notificaciones"
          value={vibracion}
          onChange={setVibracion}
          isLast
        />
      </View>

    </ScrollView>
  );
}

/* COMPONENTE REUTILIZABLE */
function NotificationItem({ title, subtitle, value, onChange, isLast }: any) {
  return (
    <View
      className={`flex-row items-center justify-between py-3 ${
        isLast ? "" : "border-b border-gray-200"
      }`}
    >
      <View className="pr-4">
        <Text className="text-base font-semibold">{title}</Text>
        <Text className="text-gray-500 text-sm">{subtitle}</Text>
      </View>

      <Switch
        value={value}
        onValueChange={onChange}
        trackColor={{ false: "#D1D5DB", true: "#2DC75C" }}
        thumbColor={value ? "#ffffff" : "#ffffff"}
      />
    </View>
  );
}