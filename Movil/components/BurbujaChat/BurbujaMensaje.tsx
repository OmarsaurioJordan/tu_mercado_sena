import React from "react";
import { StyleSheet, Text, View } from "react-native";

//este componente representa cada coso de burnuja de mensaje dentro del chat
// si se envio es verde y si es recibido es gris

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
type PropsBurbuja = {
  texto: string; // el contenido del mensaje q se va a mostrar
  esRemitente: boolean; // true si el mensaje es del usuario
};

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------

// componente funcional burbuja de mensaje recibe el texto y si es remitente o no
const BurbujaMensaje: React.FC<PropsBurbuja> = ({ texto, esRemitente }) => {
  return (
    <View
      style={[
        estilos.burbuja,// aplica el estilo base de la burbuja
        esRemitente ? estilos.remitente : estilos.receptor,
        // si es remtente es true tons aplica estilo de remitente osea el color verde
        // si no aplica estilo de receptor gris
      ]}
    >
      <Text>{texto}</Text>
      
    </View>
  );
};

const estilos = StyleSheet.create({
  burbuja: {
    padding: 10,
    marginVertical: 5,
    borderRadius: 10,
    maxWidth: "70%",
  },
  remitente: {
    alignSelf: "flex-end",
    backgroundColor: "#D0F0C0", // Verde claro
  },
  receptor: {
    alignSelf: "flex-start",
    backgroundColor: "#E0E0E0", // Gris claro
  },
});

export default BurbujaMensaje;
