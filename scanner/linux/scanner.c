#include <ctype.h>
#include <fcntl.h>
#include <linux/uinput.h>
#include <memory.h>
#include <stdbool.h>
#include <stdio.h>
#include <termios.h>
#include <unistd.h>

struct Key
{
    int  code;
    bool shift;
};



static const struct Key keymap[97] =
{
    {KEY_SPACE,      false}, // 32
    {KEY_1,          true }, // 33 !
    {KEY_APOSTROPHE, true }, // 34 "
    {KEY_3,          true }, // 35 #
    {KEY_4,          true }, // 36 $
    {KEY_5,          true }, // 37 %
    {KEY_7,          true }, // 38 &
    {KEY_APOSTROPHE, false}, // 39 '
    {KEY_9,          true }, // 40 (
    {KEY_0,          true }, // 41 )
    {KEY_8,          true }, // 42 *
    {KEY_EQUAL,      true }, // 43 +
    {KEY_COMMA,      false}, // 44 ,
    {KEY_MINUS,      false}, // 45 -
    {KEY_DOT,        false}, // 46 .
    {KEY_SLASH,      false}, // 47 /
    {KEY_0,          false}, // 48 0
    {KEY_1,          false}, // 49 1
    {KEY_2,          false}, // 50 2
    {KEY_3,          false}, // 51 3
    {KEY_4,          false}, // 52 4
    {KEY_5,          false}, // 53 5
    {KEY_6,          false}, // 54 6
    {KEY_7,          false}, // 55 7
    {KEY_8,          false}, // 56 8
    {KEY_9,          false}, // 57 9
    {KEY_SEMICOLON,  false}, // 58 :
    {KEY_SEMICOLON,  true }, // 59 ;
    {KEY_COMMA,      true }, // 60 <
    {KEY_EQUAL,      false}, // 61 =
    {KEY_DOT,        true }, // 62 >
    {KEY_SLASH,      true }, // 63 ?
    {KEY_2,          true }, // 64 @
    {KEY_A,          true }, // 65 A
    {KEY_B,          true }, // 66 B
    {KEY_C,          true }, // 67 C
    {KEY_D,          true }, // 68 D
    {KEY_E,          true }, // 69 E
    {KEY_F,          true }, // 70 F
    {KEY_G,          true }, // 71 G
    {KEY_H,          true }, // 72 H
    {KEY_I,          true }, // 73 I
    {KEY_J,          true }, // 74 J
    {KEY_K,          true }, // 75 K
    {KEY_L,          true }, // 76 L
    {KEY_M,          true }, // 77 M
    {KEY_N,          true }, // 78 N
    {KEY_O,          true }, // 79 O
    {KEY_P,          true }, // 80 P
    {KEY_Q,          true }, // 81 Q
    {KEY_R,          true }, // 82 R
    {KEY_S,          true }, // 83 S
    {KEY_T,          true }, // 84 T
    {KEY_U,          true }, // 85 U
    {KEY_V,          true }, // 86 V
    {KEY_W,          true }, // 87 W
    {KEY_X,          true }, // 88 X
    {KEY_Y,          true }, // 89 Y
    {KEY_Z,          true }, // 90 Z
    {KEY_LEFTBRACE,  false}, // 91 [
    {KEY_BACKSLASH,  false}, // 92 backslash
    {KEY_RIGHTBRACE, false}, // 93 ]
    {KEY_6,          true }, // 94 ^
    {KEY_MINUS,      true }, // 95 _
    {KEY_GRAVE,      false}, // 96 `
    {KEY_A,          false}, // 97 a
    {KEY_B,          false}, // 98 b
    {KEY_C,          false}, // 99 c
    {KEY_D,          false}, // 100 d
    {KEY_E,          false}, // 101 e
    {KEY_F,          false}, // 102 f
    {KEY_G,          false}, // 103 g
    {KEY_H,          false}, // 104 h
    {KEY_I,          false}, // 105 i
    {KEY_J,          false}, // 106 j
    {KEY_K,          false}, // 107 k
    {KEY_L,          false}, // 108 l
    {KEY_M,          false}, // 109 m
    {KEY_N,          false}, // 110 n
    {KEY_O,          false}, // 111 o
    {KEY_P,          false}, // 112 p
    {KEY_Q,          false}, // 113 q
    {KEY_R,          false}, // 114 r
    {KEY_S,          false}, // 115 s
    {KEY_T,          false}, // 116 t
    {KEY_U,          false}, // 117 u
    {KEY_V,          false}, // 118 v
    {KEY_W,          false}, // 119 w
    {KEY_X,          false}, // 120 x
    {KEY_Y,          false}, // 121 y
    {KEY_Z,          false}, // 122 z
    {KEY_LEFTBRACE,  true }, // 123 {
    {KEY_BACKSLASH,  true }, // 124 |
    {KEY_RIGHTBRACE, true }, // 125 }
    {KEY_GRAVE,      true }, // 126 ~
    {KEY_DELETE,     false}, // 127
    {KEY_ENTER,      false}, //
};

const int sleepus = 1000;

void press_shift(int fd)
{
    struct input_event ev = {0};

    memset(&ev, 0, sizeof(ev));

    ev.type  = EV_KEY;
    ev.code  = KEY_LEFTSHIFT;
    ev.value = 1;
    write(fd, &ev, sizeof(ev));
    ev.type  = EV_SYN;
    ev.code  = SYN_REPORT;
    ev.value = 0;
    write(fd, &ev, sizeof(ev));
    usleep(sleepus);
}

void release_shift(int fd)
{
    struct input_event ev = {0};

    memset(&ev, 0, sizeof(ev));

    ev.type  = EV_KEY;
    ev.code  = KEY_LEFTSHIFT;
    ev.value = 0;
    write(fd, &ev, sizeof(ev));
    ev.type  = EV_SYN;
    ev.code  = SYN_REPORT;
    ev.value = 0;
    write(fd, &ev, sizeof(ev));
    usleep(sleepus);
}

void send_key(const int fd, const int keyindex)
{
    struct input_event event = {0};
    const int key    = keymap[keyindex].code;
    const bool shift = keymap[keyindex].shift;

    if (shift)
    {
        press_shift(fd);
    }

    event.type  = EV_KEY;
    event.value = 1;
    event.code  = key;
    write(fd, &event, sizeof(event));

    event.type  = EV_SYN;
    event.code  = SYN_REPORT;
    event.value = 0;
    write(fd, &event, sizeof(event));

    usleep(sleepus);

    event.type  = EV_KEY;
    event.value = 0;
    event.code  = key;
    write(fd, &event, sizeof(event));

    event.type  = EV_SYN;
    event.code  = SYN_REPORT;
    event.value = 0;
    write(fd, &event, sizeof(event));

    usleep(sleepus);

    if (shift)
    {
        release_shift(fd);
    }
}

int main()
{
    // Open serial port
    int fd = open("/dev/ttyACM0", O_RDWR | O_NOCTTY);

    if (fd < 0)
    {
        printf("Error opening serial port\n");
        return 1;
    }

    // Configure serial port
    struct termios options;

    tcgetattr(fd, &options);
    options.c_cflag = B9600 | CS8 | CLOCAL | CREAD;
    options.c_iflag = IGNPAR;
    options.c_oflag = 0;
    options.c_lflag = 0;
    tcflush(fd, TCIFLUSH);
    tcsetattr(fd, TCSANOW, &options);

    // Open uinput device
    int uinput_fd = open("/dev/uinput", O_WRONLY | O_NONBLOCK);

    if (uinput_fd < 0)
    {
        printf("Error opening uinput device\n");
        return 1;
    }

    // Configure uinput device
    struct uinput_user_dev uinput_dev;

    memset(&uinput_dev, 0, sizeof(uinput_dev));
    snprintf(uinput_dev.name, UINPUT_MAX_NAME_SIZE, "uinput-scanner");
    uinput_dev.id.bustype = BUS_VIRTUAL;
    uinput_dev.id.vendor  = 0x5234;
    uinput_dev.id.product = 0x1247;
    uinput_dev.id.version = 1;
    ioctl(uinput_fd, UI_SET_EVBIT, EV_SYN);
    ioctl(uinput_fd, UI_SET_EVBIT, EV_KEY);
    for (int i=0; i < (sizeof(keymap) / sizeof(struct Key)); i++)
    {
        // printf("key = %d\n",keymap[i].code);
        ioctl(uinput_fd, UI_SET_KEYBIT, keymap[i].code);
    }
    ioctl(uinput_fd, UI_SET_KEYBIT, KEY_LEFTSHIFT);
    write(uinput_fd, &uinput_dev, sizeof(uinput_dev));
    ioctl(uinput_fd, UI_DEV_CREATE);

    sleep(3);

    // Read data from serial port and type on virtual keyboard
    char c;

    while (read(fd, &c, 1) > 0)
    {
        if (iscntrl(c) && c != '\n' && c != '\r')
        {
            c = '?';
        }

        // Type character on virtual keyboard
        if (c >= 32 || c == '\n' || c == '\r')
        {
            int i = c - 32;
            if (c == '\n' || c == '\r')
            {
                i = 96;
            }
            if (i < (sizeof(keymap) / sizeof(struct Key)))
            {
                printf("key = %d\n",keymap[i].code);
                send_key(uinput_fd, i);
            }
        }
    }

    // Destroy uinput device and close serial port
    ioctl(uinput_fd, UI_DEV_DESTROY);
    close(uinput_fd);
    close(fd);

    return 0;
}
