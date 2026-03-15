import { Ionicons } from "@expo/vector-icons";
import React, { useEffect, useMemo, useRef, useState } from "react";
import { Animated, Text, View } from "react-native";

import CustomButton from "@/components/buttons/CustomButton";
import CustomInput from "@/components/inputs/CustomInput";

type Step = 1 | 2 | 3 | 4;

type Props = {
  onDone?: () => void;   // cerrar modal al final
//   onCancel?: () => void; // cerrar modal cuando quieras
};

function SuccessStep({ onFinish }: { onFinish?: () => void }) {
  const scale = useRef(new Animated.Value(0.6)).current;
  const opacity = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(opacity, {
        toValue: 1,
        duration: 250,
        useNativeDriver: true,
      }),
      Animated.sequence([
        Animated.spring(scale, {
          toValue: 1.05,
          friction: 5,
          useNativeDriver: true,
        }),
        Animated.spring(scale, {
          toValue: 1,
          friction: 6,
          useNativeDriver: true,
        }),
      ]),
    ]).start();

    const t = setTimeout(() => onFinish?.(), 2500); // autocerrar
    return () => clearTimeout(t);
  }, [onFinish, opacity, scale]);

  return (
    <View
    style={{
      width: "100%",
      alignSelf: "stretch",
      minHeight: 320,                 // 👈 clave para Android (dale espacio real)
      justifyContent: "center",
      alignItems: "center",
      paddingHorizontal: 24,
      paddingTop: 60,                 // 👈 si lo quieres más abajo
    }}
  >
    <Animated.View style={{ transform: [{ scale }], opacity }}>
      <Ionicons name="checkmark-circle" size={72} color="#22C55E" />
    </Animated.View>

    <Animated.Text
      style={{
        opacity,
        marginTop: 16,
        fontSize: 22,
        fontWeight: "700",
        color: "#111827",
        textAlign: "center",
      }}
    >
      Contraseña cambiada correctamente
    </Animated.Text>

    <Animated.Text
      style={{
        opacity,
        marginTop: 8,
        fontSize: 14,
        color: "#6B7280",
        textAlign: "center",
      }}
    >
      Ya puedes iniciar sesión con tu nueva contraseña.
    </Animated.Text>
  </View>
);
}

export default function ResetPasswordFlow({ onDone }: Props) {

    
  const [step, setStep] = useState<Step>(1);

  // campos UI (sin BD)
  const [email, setEmail] = useState("");
  const [token, setToken] = useState("");
  const [newPass, setNewPass] = useState("");
  const [confirmPass, setConfirmPass] = useState("");

  // mensajes UI
  const [error, setError] = useState<string | null>(null);
  const [info, setInfo] = useState<string | null>(null);

  const canGoEmail = useMemo(() => email.trim().length >= 5, [email]);
  const canGoToken = useMemo(() => token.trim().length >= 4, [token]);
  const canGoPass = useMemo(
    () => newPass.length >= 6 && confirmPass.length >= 6,
    [newPass, confirmPass]
  );

  const resetMessages = () => {
    setError(null);
    setInfo(null);
  };

  const handleSendCode = () => {
    resetMessages();
    if (!canGoEmail) {
      setError("Ingresa un correo válido para continuar.");
      return;
    }

    // UI fake: “enviamos” el código
    setInfo("Te enviamos un código de verificación (simulado).");
    setStep(2);
  };

  const handleVerifyToken = () => {
    resetMessages();
    if (!canGoToken) {
      setError("Ingresa el código para continuar.");
      return;
    }

    // UI fake: aceptamos cualquier token de 4+ caracteres
    setInfo("Código verificado (simulado).");
    setStep(3);
  };

  const handleChangePassword = () => {
    resetMessages();

    if (!canGoPass) {
      setError("La contraseña debe tener al menos 6 caracteres.");
      return;
    }

    if (newPass !== confirmPass) {
      setError("Las contraseñas no coinciden.");
      return;
    }

    // UI fake: “cambiamos” contraseña
    setInfo(null);
    setError(null);
    setStep(4);
  };

  const goBack = () => {
    resetMessages();
    setStep((prev) => (prev === 1 ? 1 : ((prev - 1) as Step)));
  };

  return (
    <View>
      {/* Header del modal */}
      <View className="mt-8">
        <Text className="font-Opensans-bold text-2xl text-black">
          Recuperar contraseña
        </Text>

        <Text className="text-gray-400 mt-2">
          {step === 1 && "Ingresa tu correo institucional para enviar el código."}
          {step === 2 && "Ingresa el código que recibiste por correo."}
          {step === 3 && "Crea tu nueva contraseña y confírmala."}
          {step === 4 && ( <SuccessStep onFinish={onDone}/> )}
        </Text>
      </View>

      {/* Mensajes */}
      {!!error && (
        <View className="mt-4 p-3 rounded-xl bg-red-50 border border-red-200">
          <Text className="text-red-700">{error}</Text>
        </View>
      )}

      {!!info && (
        <View className="mt-4 p-3 rounded-xl bg-green-50 border border-green-200">
          <Text className="text-green-700">{info}</Text>
        </View>
      )}

      {/* Paso 1: Email */}
      {step === 1 && (
        <View className="mt-8">
          <Text className="text-2xl font-Opensans-medium text-black mb-2">
            Correo institucional
          </Text>

          <CustomInput
            className="p-1.5"
            placeholder="Ejemplo@sena.edu.co"
            placeholderTextColor="#CDCDCD"
            type="email"
            // Si tu CustomInput soporta onChangeText/value, úsalo:
            value={email}
            onChangeText={setEmail}
            icon={<Ionicons name="mail-outline" size={20} color="#9CA3AF" />}
          />

          <View className="items-center mt-6">
            <CustomButton
              variant="contained"
              className="w-full p-5 rounded-r-full rounded-l-full border border-[#2DC75C]"
              FontText="text-2xl"
              color="sextary"
              icon={<Ionicons name="send" size={22} color="#000000" />}
              onPress={handleSendCode}
            >
              Enviar código
            </CustomButton>
          </View>

        </View>
      )}

      {/* Paso 2: Token */}
      {step === 2 && (
        <View className="mt-8">
          <Text className="text-2xl font-Opensans-medium text-black mb-2">
            Código de verificación
          </Text>

          <CustomInput
            className="p-1.5"
            placeholder="Ej: 123456"
            placeholderTextColor="#CDCDCD"
            type="text"
            value={token}
            onChangeText={setToken}
            icon={<Ionicons name="key-outline" size={20} color="#9CA3AF" />}
          />

          <View className="items-center mt-6">
            <CustomButton
              variant="contained"
              className="w-full p-5 rounded-3xl border border-[#2DC75C]"
              FontText="text-2xl"
              color="sextary"
              icon={<Ionicons name="shield-checkmark-outline" size={22} color="#000000" />}
              onPress={handleVerifyToken}
            >
              Verificar token
            </CustomButton>
          </View>

          <View className="flex-row justify-between items-center mt-4">
            <CustomButton
              variant="text-only"
              color="secondary"
              FontText="text-xl"
              underline
              onPress={goBack}
              icon={<Ionicons name="arrow-back" size={20} color="#1C65E3" />}
              iconPosition='center'
            >
              Volver
            </CustomButton>

          </View>
        </View>
      )}

      {/* Paso 3: New password */}
      {step === 3 && (
        <View className="mt-8">
          <Text className="text-2xl font-Opensans-medium text-black mb-2">
            Nueva contraseña
          </Text>

          <CustomInput
            className="p-1.5"
            placeholder="Mínimo 6 caracteres"
            placeholderTextColor="#CDCDCD"
            type="password"
            value={newPass}
            onChangeText={setNewPass}
            icon={<Ionicons name="lock-closed-outline" size={20} color="#9CA3AF" />}
          />

          <Text className="text-2xl font-Opensans-medium text-black mt-6 mb-2">
            Confirmar contraseña
          </Text>

          <CustomInput
            className="p-1.5"
            placeholder="Repite la contraseña"
            placeholderTextColor="#CDCDCD"
            type="password"
            value={confirmPass}
            onChangeText={setConfirmPass}
            icon={<Ionicons name="lock-closed-outline" size={20} color="#9CA3AF" />}
          />

          <View className="items-center mt-6">
            <CustomButton
              variant="contained"
              className="w-full p-5 rounded-3xl border border-[#2DC75C]"
              FontText="text-2xl"
              color="sextary"
              icon={<Ionicons name="checkmark-circle-outline" size={22} color="#000000" />}
              onPress={handleChangePassword}
            >
              Cambiar contraseña
            </CustomButton>
          </View>

          <View className="flex-row justify-between items-center mt-4">
            {/* <CustomButton
              variant="text-only"
              color="secondary"
              FontText="text-xl"
              underline
              onPress={goBack}
              icon={<Ionicons name="arrow-back" size={20} color="#1C65E3" />}
              iconPosition='left'
            >
              Volver
            </CustomButton> */}

          </View>
        </View>
      )}
    </View>
  );
}
