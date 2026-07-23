export const tr = {
  app: {
    name: 'Okul Yönetim Sistemi',
  },

  routeTitles: {
    login: 'Giriş Yap',
    register: 'Kayıt Ol',
    dashboard: 'Anasayfa',
    profile: 'Profil Bilgilerim',
  },

  nav: {
    profile: 'Profilim',
    logout: 'Çıkış Yap',
    backToDashboard: 'Anasayfaya dön',
  },

  errors: {
    unexpected: 'Beklenmeyen bir hata oluştu.',
    unexpectedRetry: 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.',
    network: 'Sunucuya ulaşılamadı. Bağlantınızı kontrol edin.',
    profileLoad: 'Profil bilgileri yüklenemedi.',
  },

  fields: {
    email: 'E-posta',
    emailPlaceholder: 'ornek@mail.com',
    password: 'Şifre',
    name: 'Ad',
    surname: 'Soyad',
    passwordShow: 'Şifreyi göster',
    passwordHide: 'Şifreyi gizle',
  },

  validation: {
    emailRequired: 'E-posta adresi zorunludur',
    emailMax: 'E-posta adresi en fazla 100 karakter olabilir',
    emailInvalid: 'Geçerli bir e-posta adresi girin',
    passwordRequired: 'Şifre zorunludur',
    passwordMin: 'Şifre en az 6 karakter olmalıdır',
    passwordConfirmRequired: 'Şifre tekrarı zorunludur',
    passwordMismatch: 'Şifreler eşleşmiyor',
    nameRequired: 'Ad zorunludur',
    nameMax: 'Ad en fazla 100 karakter olabilir',
    nameAlphaNum: 'Ad sadece harf ve rakam içerebilir',
    surnameRequired: 'Soyad zorunludur',
    surnameMax: 'Soyad en fazla 100 karakter olabilir',
    surnameAlphaNum: 'Soyad sadece harf ve rakam içerebilir',
    codeRequired: 'Kayıt kodu zorunludur',
    codeMin: 'Kayıt kodu en az 6 karakter olmalıdır',
  },

  login: {
    title: 'Giriş Yap',
    description: 'Hesabınıza erişmek için e-posta ve şifrenizi giriniz',
    registeredSuccess: 'Kaydınız oluşturuldu. Şimdi giriş yapabilirsiniz.',
    submit: 'Giriş Yap',
    submitting: 'Giriş yapılıyor...',
    noAccount: 'Hesabınız yok mu?',
    registerLink: 'Kayıt olun',
  },

  register: {
    title: 'Kayıt Ol',
    description: 'Öğrenci kayıt kodunuz ile veli hesabı oluşturun',
    namePlaceholder: 'Ayşe',
    surnamePlaceholder: 'Yılmaz',
    codeLabel: 'Kayıt Kodu',
    codePlaceholder: 'ABC123',
    codeHint: 'Okulunuzdan aldığınız öğrenci kayıt kodunu girin',
    passwordConfirmLabel: 'Şifre Tekrar',
    submit: 'Hesap Oluştur',
    submitting: 'Oluşturuluyor...',
    haveAccount: 'Zaten hesabınız var mı?',
    loginLink: 'Giriş yapın',
  },

  profile: {
    infoTitle: 'Profil Bilgilerim',
    infoDescription: 'Hesap bilgilerinizi güncelleyin',
    passwordTitle: 'Şifre Değiştir',
    passwordDescription: 'Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın',
    newPassword: 'Yeni Şifre',
    newPasswordConfirm: 'Yeni Şifre Tekrar',
    passwordHint: 'Yeni şifreniz en az 6 karakter olmalı',
    reset: 'Sıfırla',
    save: 'Değişiklikleri Kaydet',
    saving: 'Kaydediliyor...',
    saved: 'Bilgileriniz güncellendi.',
    noChanges: 'Değiştirilecek bir bilgi yok.',
  },

  roles: {
    ROLE_ADMIN: 'Yönetici',
    ROLE_USER: 'Veli',
    unknown: '-',
  },

  account: {
    title: 'Hesap Bilgilerim',
    fullName: 'Ad Soyad',
    email: 'E-posta',
    role: 'Hesap Tipi',
  },

  myStudents: {
    title: 'Öğrencilerim',
    description: 'Hesabınıza bağlı öğrenciler',
    empty: 'Kayıtlı öğrenci bulunmuyor.',
    numberLabel: 'Numara',
    gradeLabel: 'Sınıf',
    notSpecified: '-',
  },

  students: {
    title: 'Öğrenciler',
    description: 'Sistemdeki öğrenciler ve ilişkili veli bilgileri',
    searchPlaceholder: 'Ad, numara veya kayıt kodu ara',
    searchLabel: 'Öğrenci ara',
    loadError: 'Öğrenciler yüklenemedi.',
    empty: 'Kayıtlı öğrenci bulunmuyor.',
    emptySearch: 'Aramanızla eşleşen öğrenci bulunamadı.',
    columns: {
      student: 'Öğrenci',
      number: 'Numara',
      grade: 'Sınıf',
      code: 'Kayıt Kodu',
      parent: 'Veli',
      actions: 'İşlemler',
    },
    generateCode: 'Kod Üret',
    copyCode: 'Kodu kopyala',
    copied: 'Kopyalandı',
    copiedAria: 'Kod kopyalandı',
    copyFailed: 'Kod kopyalanamadı. Kodu elle seçip kopyalayabilirsiniz.',
    pagination: {
      total: (count: number) => `Toplam ${count} kayıt`,
      perPage: 'Sayfa başına',
      pageInfo: (current: number, total: number) => `Sayfa ${current} / ${total}`,
      firstPage: 'İlk sayfa',
      prevPage: 'Önceki sayfa',
      nextPage: 'Sonraki sayfa',
      lastPage: 'Son sayfa',
    },
  },
}
