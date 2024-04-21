//
//  ContentView.swift
//  TellMe
//
//  Created by Antonio Blanco Oliva on 8/2/24.
//

import SwiftUI
import AVFoundation

struct ContentView: View {
    @ObservedObject var audioRecorder: AudioRecorder
    @State private var isRecording = false
    @State private var audioPlayer: AVAudioPlayer?

     @State private var feedbackMessage = ""
    @State private var showFeedback = false // Para controlar la visibilidad del mensaje de feedback

@State private var hasRecorded = false


var body: some View {
    ZStack {
    Color(red: 169/255, green: 187/255, blue: 188/255, opacity: 1)
        .edgesIgnoringSafeArea(.all)

        VStack(spacing: 20) {
            // añadimos el appIcon de la app
            Image("logo")
                .resizable()
                .aspectRatio(contentMode: .fit)
                .frame(width: 200, height: 200)
                .padding()
            HStack {
                Button(action: {
                    self.isRecording.toggle()
                    if self.isRecording {
                        self.audioRecorder.startRecording()
                    } else {
                        self.audioRecorder.stopRecording()
                        self.hasRecorded = true
                    }
                }) {
                    Text(isRecording ? "Stop Recording" : "Start Recording")
                        .font(.headline)
                        .foregroundColor(.white)
                        .padding()
                        .frame(minWidth: 0, maxWidth: 200)
                        .background(isRecording ? Color.red : Color.blue)
                        .cornerRadius(20)
                        .padding(.horizontal)
                }
                .accessibility(label: Text(isRecording ? "Stop Recording" : "Start Recording"))
                .padding()

                if hasRecorded {
                    Button(action: playRecording) {
                    Image(systemName: "play.fill")
                        .font(.title)
                        .foregroundColor(.white)
                        .padding(10)
                        .background(Color.blue.opacity(0.6))
                        .cornerRadius(30)
                }
                .accessibility(label: Text("Play Recording"))
                .padding()
                }
            }

            if hasRecorded {
                Button(action: {
                    uploadAudio(fileURL: self.audioRecorder.getRecordingURL()!, endpointURL: "https://your-domain.com/wp-json/tellme/v1/audio")
                }) {
                    HStack {
                Image(systemName: "paperplane.fill")
                    .font(.title)
                Text("Enviar audio")
                    .font(.headline)
            }
            .foregroundColor(.white)
            .padding()
            .frame(minWidth: 0, maxWidth: .infinity)
            .background(Color.green)
            .cornerRadius(20)
            .padding(.horizontal)
                }
                .accessibility(label: Text("Enviar audio"))
                .padding()
            }

            // feedback
            if showFeedback {
                Text(feedbackMessage)
                    .font(.headline)
                    .foregroundColor(.white)
                    .padding()
                    .background(Color.green.opacity(0.6))
                    .cornerRadius(0)
                    .padding(.horizontal)
            }
        }
    }
}


    func uploadAudio(fileURL: URL, endpointURL: String) {
        let boundary = "Boundary-\(UUID().uuidString)"
        var request = URLRequest(url: URL(string: endpointURL)!)
        request.httpMethod = "POST"
        
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")

        var data = Data()
        
        data.append("--\(boundary)\r\n".data(using: .utf8)!)
        data.append("Content-Disposition: form-data; name=\"file\"; filename=\"\(fileURL.lastPathComponent)\"\r\n".data(using: .utf8)!)
        data.append("Content-Type: audio/m4a\r\n\r\n".data(using: .utf8)!)
        if let audioData = try? Data(contentsOf: fileURL) {
            data.append(audioData)
        }
        data.append("\r\n--\(boundary)--\r\n".data(using: .utf8)!)

        request.httpBody = data
        request.setValue(String(data.count), forHTTPHeaderField: "Content-Length")
        
        let session = URLSession.shared
        let task = session.dataTask(with: request) { data, response, error in
            if let error = error {
                self.feedbackMessage = "Error al enviar el archivo: \(error.localizedDescription)"
                self.showFeedback = true
                return
            }
            
            if let response = response as? HTTPURLResponse, response.statusCode == 200 {
                print("Archivo enviado con éxito")
                self.feedbackMessage = "Archivo enviado con éxito"
            } else {
                print("Error en la respuesta del servidor")
                self.feedbackMessage = "Error en la respuesta del servidor"
            }
            self.showFeedback = true
        }
        task.resume()
    }

    
    func playRecording() {
        let playbackSession = AVAudioSession.sharedInstance()
        do {
            try playbackSession.overrideOutputAudioPort(AVAudioSession.PortOverride.speaker)
        } catch {
            print("Playing over the device's speakers failed")
        }
        
        guard let url = self.audioRecorder.getRecordingURL() else { return }
        do {
            audioPlayer = try AVAudioPlayer(contentsOf: url)
            audioPlayer?.play()
        } catch {
            print("Playback failed.")
        }
    }
}

struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView(audioRecorder: AudioRecorder())
    }
}
