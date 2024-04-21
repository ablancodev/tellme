//
//  AudioRecorder.swift
//  TellMe
//
//  Created by Antonio Blanco Oliva on 8/2/24.
//

import Foundation
import AVFoundation

class AudioRecorder: NSObject, ObservableObject {
    var audioRecorder: AVAudioRecorder?
    var recordings = [Recording]()
    var currentRecordingURL: URL?

    override init() {
        super.init()
        fetchRecordings()
    }

    func startRecording() {
        let recordingSession = AVAudioSession.sharedInstance()
        do {
            try recordingSession.setCategory(.playAndRecord, mode: .default)
            try recordingSession.setActive(true)
        } catch {
            print("Failed to set up recording session")
        }

        let documentPath = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
        let audioFilename = documentPath.appendingPathComponent("\(UUID().uuidString).m4a")
        self.currentRecordingURL = audioFilename

        let settings = [
            AVFormatIDKey: Int(kAudioFormatMPEG4AAC),
            AVSampleRateKey: 12000,
            AVNumberOfChannelsKey: 1,
            AVEncoderAudioQualityKey: AVAudioQuality.high.rawValue
        ]

        do {
            audioRecorder = try AVAudioRecorder(url: audioFilename, settings: settings)
            audioRecorder?.delegate = self
            audioRecorder?.record()
        } catch {
            print("Could not start recording")
        }
    }

    func stopRecording() {
        audioRecorder?.stop()
        audioRecorder = nil
        fetchRecordings()
    }

    func getRecordingURL() -> URL? {
        return currentRecordingURL
    }

    func fetchRecordings() {
        recordings.removeAll()

        let documentPath = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
        let directoryContents = try? FileManager.default.contentsOfDirectory(at: documentPath, includingPropertiesForKeys: nil)

        if let directoryContents = directoryContents {
            for file in directoryContents {
                let recording = Recording(fileURL: file, createdAt: getCreationDate(for: file))
                recordings.append(recording)
            }
        }

        recordings.sort(by: { $0.createdAt.compare($1.createdAt) == .orderedDescending })
    }

    private func getCreationDate(for file: URL) -> Date {
        if let attributes = try? FileManager.default.attributesOfItem(atPath: file.path) as [FileAttributeKey: Any],
           let creationDate = attributes[FileAttributeKey.creationDate] as? Date {
            return creationDate
        } else {
            return Date()
        }
    }
}

extension AudioRecorder: AVAudioRecorderDelegate {
    func audioRecorderDidFinishRecording(_ recorder: AVAudioRecorder, successfully flag: Bool) {
        if flag {
            fetchRecordings()
        }
    }
}

struct Recording {
    let fileURL: URL
    let createdAt: Date
}
